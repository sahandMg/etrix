<?php

namespace App\Http\Controllers;

use App\Bom;
use App\Cart;
use App\Address;
use App\Exports\CartsExport;
use App\Imports\CartsImport;
use App\Repository\URls;
use App\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Morilog\Jalali\Jalalian;

class CartController extends Controller
{
    /**
     * Controls Carts + BOMs + Projects
     * @internal param Request $request
     */
    public $cart = [];
    public $delivery = 10000;
    public function __construct()
    {
        $this->middleware('guest');
    }
/*
 * Required Params => cart,token
 * Format to receive => cart {
    {
            "cart": [
                [
        {
        "keyword":"STM32F103CBT6",
        "num":"1",
        "project":""
        },
        {
        "keyword":"STM32F429ZGT6",
        "num":"1",
        "project":""
        }
        ],
        [
            {
        "keyword":"STM32F100C8T6B",
        "num":"2",
        "project":"robo"
        }
            ]
            ],
        "token":""
    }
 *
 * Gets guest cart. then adds it to the authenticated guest account
 * use createCart method
 *
 */
    public function addToCart(Request $request){

        $fault = [];
        $added = [];
        $carts = $request->cart;
        for($i=0;$i<count($carts);$i++){
            for($t=0;$t<count($carts[$i]);$t++){
                unset($request['cart']);
                $request['keyword'] = $carts[$i][$t]['keyword'];
                $request['num'] = $carts[$i][$t]['num'];
                $request['project'] = $carts[$i][$t]['project'];
                $this->cart = [];
                $resp = $this->createCart($request);
//                Checks if quantity is available
                if($resp['code'] != 200){

                     array_push($fault,$resp['body'][0]);
                }else{
                   array_push($added,$request['keyword']);
                }
            }
        }


        if(count($fault) != 0){
            return ['body'=>$fault,'code'=>404];
        }else{

            return ['body' => $added ,
                'code' => 200
            ];
        }
    }

    /*
     * Required Params => num , keyword , token , project = (NULL || Project name)
     * check parts price from price api
     * check parts availability with the requested number
     * Get project_id from project name in $request->project
     */
    public function createCart(Request $request,$arr=null){
        /**
         * BOM Bill Of Materials
         * status = 0 -> BOM : open
         * status = 50 -> BOM : closed
         * status = 100 -> BOM : closed
         */

        if(!is_null($arr)){
            $request['keyword'] = $arr['keyword'][0];
            $request['num'] = $arr['num'][0];
            if(!is_null($arr['project'])){
                $request['project'] = $arr['project'];
            }
        }

        if(!is_null($request->project)){
            $project = DB::table('projects')->where('name', $request->project)->where('user_id',  Auth::guard('user')->id())->first();
            if($project){
                $projectId = $project->id;
            }else{
                return ['body'=>'project not found',
                'code'=>404
                ];
            }
        }
    try{
        if(count(Auth::user()->boms) > 0 ){
            $bom = Bom::where('user_id', Auth::guard('user')->id())->orderBy('created_at','decs')->first();
            /*
             * status != 0 means that the last BOM has closed
             */

            if($bom->status != 0){
                $bom = new Bom();
                $bom->status = 0;
                $bom->user_id =  Auth::guard('user')->id();
                $bom->delivery = $this->delivery;
                $bom->price = 0;
                $bom->order_number = rand(100,10000);
                $bom->save();
            }
        }else{
            $bom = new Bom();
            $bom->status = 0;
            $bom->user_id =  Auth::guard('user')->id();
            $bom->price = 0;
            $bom->delivery = $this->delivery;
            $bom->order_number = rand(100,10000);
            $bom->save();
        }
    }catch (\Exception $exception){
        return $exception;
    }

        $quantity = get_object_vars(DB::table('commons')->where('manufacturer_part_number',$request->keyword)->first())['quantity_available'];

        if( $quantity < $request->num){

            return['body' =>[ 'موجود نمی باشد'.' '.$request->keyword.' '.'در حال حاضر این تعداد از'],
                'code'=>404];
        }

        array_push($this->cart ,[
            'keyword'  =>  $request->keyword,
            'num'   =>  $request->num,
            'project' => $request->project
        ]);


        $orders = DB::table('carts')->where('bom_id',$bom->id)->get();
        /*
         * New order registration
         */

        if(count($orders) == 0){
            try{
                $cart = new Cart();
                $cart->name = serialize($this->cart);
                $cart->bom_id = $bom->id;
                if (!is_null($request->project)) {
                    $projectId = DB::table('projects')->where('user_id',Auth::guard('user')->id())->where('name', $request->project)->first()->id;
                    $cart->project_id = $projectId;
                }
                $cart->save();
                $resp = $this->availability($request->num);
                if($resp['code'] == 200){
                    return ['body' => "$request->keyword قطعه اضافه شد  " ,
                        'code' => 200
                    ];
                }else{
                    return $resp;
                }

            }catch (\Exception $exception){
                return $exception;
            }

        }
        /*
         * Update last order
         */
        else{
            /*
            * TODO if user , orders parts for two or more projects at a same time then ???
             * checks if project has been created or not
            */
            if (!is_null($request->project)) {

                $project = DB::table('projects')->where('name', $request->project)->where('user_id',  Auth::guard('user')->id())->first();
                if($project){
                    $projectId = $project->id;
                }else{
                    return 'project not found';
                }
                foreach ($orders as $order){
                    if($order->project_id == $projectId){
                        $userOrder = $order;
                    }
                }
                if(!isset($userOrder)){
                    /*
                     * create separate cart for project
                     */
                    $cart = new Cart();
                    $cart->name = serialize($this->cart);
                    $cart->bom_id = $bom->id;
                    $cart->project_id = $projectId;
                    $cart->save();
                    $resp = $this->availability($request->num);
                    if($resp['code'] == 200){
                        return ['body' => "$request->keyword قطعه اضافه شد  " ,
                            'code' => 200
                        ];
                    }else{
                        return $resp;
                    }

                }else{
                    /*
                     * update the project cart
                     */

                    $this->updateCart($userOrder,$request,$bom);
                    $resp = $this->availability($request->num);
                    if($resp['code'] == 200){
                        return ['body' => "$request->keyword قطعه اضافه شد  " ,
                            'code' => 200
                        ];
                    }else{
                        return $resp;
                    }

                }

//
//
            }else{

                foreach ($orders as $order){
                    if($order->project_id == 0 ){
                        $userOrder = $order;
                    }
                }

                if(isset($userOrder)) {
                    $this->updateCart($userOrder,$request,$bom);
                }else{
                    $cart = new Cart();
                    $cart->name = serialize($this->cart);
                    $cart->bom_id = $bom->id;
                    $cart->save();

                }
                $resp = $this->availability($request->num);
                if($resp['code'] == 200){
                    return ['body' => "$request->keyword قطعه اضافه شد  " ,
                        'code' => 200
                    ];
                }else{
                    return $resp;
                }

            }

            /*
             * update a cart data without project_id
             * check all carts name for a given BOM id and a given part name
             */

        }

    }

    /* Getting new Prices when ever the cart page, gets refreshed

        Required Params => token
        get total parts number in all projects and do a comparison
         to the available number in database
    */
    public function readCart(Request $request){

        try{
            $bom = Bom::where('user_id', Auth::guard('user')->id())->where('status',0)->firstOrFail();
        }catch (\Exception $exception){
            return [];
        }
        if(count($bom->carts) != 0){
            $carts = $bom->carts;

        }else{
            return [];
        }

        for($i=0 ; $i<count($carts) ;$i++){
            $orders[$i] = array_values(unserialize($carts[$i]->name));

            for($t=0 ; $t<count($orders[$i]);$t++){

                $request['keyword'] = $orders[$i][$t]['keyword'];
                $ctrl = new SearchController();
//                    /*
//                     *
//                     * TODO get_object_vars() --> cannot use object of type stdclass as array
//                     * if part could not be found
//                     */

                if(gettype($ctrl->getPrice($request)) == 'integer'){
                    $orders[$i][$t]['price'] = 0;
                }
                else{

                    $vars = json_decode($ctrl->getPrice($request),true);
                    $orders[$i][$t]['price'] =  $vars['unit_price'];
                    if($carts[$i]->project_id != 0 ){

                        $projs[$i] = DB::table('projects')->where('id',$carts[$i]->project_id)->first()->name;
                        $orders[$i][$t]['project'] =  $projs[$i];

                    }
                    else{
                        $orders[$i][$t]['project'] = null;
                    }

                }
                if($orders[$i][$t]['price'] == null){
                    $orders[$i][$t]['price'] = 0;
                }

            }


        }
        return array_values(array_filter($orders));



    }
/*
 *
 *  uses for updating cart data
 */

    protected function updateCart($userOrder,$request,$bom)
    {
  //TODO all carts in a Bom will get update :(

        if (isset($userOrder)) {
            session()->forget('check');
            $cartArray = array_values(unserialize($userOrder->name));
            for ($i = 0; $i < count($cartArray); $i++) {
                if ($cartArray[$i]['keyword'] == $request->keyword) {
                    session()->put('check' ,$i);
                }
            }
            if (session()->has('check')) {
                $quantity = get_object_vars(DB::table('commons')->where('manufacturer_part_number',$cartArray[session('check')]['keyword'])->first())['quantity_available'];
                if( $quantity < $cartArray[session('check')]['num']){
                    return 'موجود نمی باشد'.' '.$request->keyword.' '.'در حال حاضر این تعداد از';
                }
                $cartArray[session('check')]['num'] = $cartArray[session('check')]['num'] + $request->num;
                DB::table('carts')->where('bom_id', $bom->id)
                    ->where('project_id',$userOrder->project_id)
                    ->update(['name' => serialize($cartArray)]);

            } else {
                array_push($cartArray, $this->cart[0]);
                DB::table('carts')->where('bom_id', $bom->id)
                    ->where('project_id',$userOrder->project_id)
                    ->update(['name' => serialize($cartArray)]);

            }
        }
    }

    protected function cartWithoutToken($request){
        /*
         * Create a bom query with user_id = 0
         * create cart with user_id = 0
         * use session to store user cart
         */


        if(!session()->has('guestBom')){

            $bom = new Bom();
            $bom->status = 0;
            $bom->delivery = $this->delivery;
            $bom->order_number = rand(100,10000);
            $bom->save();
            session()->put(['guestBom'=>$bom]);
        }



        array_push($this->cart ,[
            'keyword'  =>  $request->keyword,
            'num'   =>  $request->num,
        ]);

        $order = DB::table('carts')->where('bom_id',$bom->id)->first();
        /*
         * New order registration
         */
        if(!$order){
            $cart = new Cart();
            $cart->name = serialize($this->cart);
            $cart->bom_id = $bom->id;
            $cart->save();

            return 200;
        }
        /*
         * Update last order
         */
        else{
            /*
            * TODO if user , orders parts for two or more projects at a same time then ???
            */


                    /*
                     * update the project cart
                     */

                    $this->updateCart($order,$request,$bom);
                return 200;
            }

            /*
             * update a cart data without project_id
             * check all carts name for a given BOM id and a given part name
             */


    }

    // Deletes an item in a cart using a keyword and a project name
    /*
     *  Required Params => project,keyword,token
     *  Send => remained parts in cart
     */

    public function editCart(Request $request){
        $content = [];
        $sign = 0;
        if(!is_null($request->project)){
            try{
                $id = DB::table('projects')->where('user_id', Auth::guard('user')->id())->where('name',$request->project)->first()->id;
            }catch (\Exception $exception){
                return 'project not found';
            }

            $project_name = DB::table('projects')->where('name',$request->project)->first()->name;
            $cart = Bom::where('user_id', Auth::guard('user')->id())->where('status',0)->first()->carts->where('project_id',$id)->first();
            if(is_null($cart)){
                return 'cart not found';
            }
           $items = array_values(unserialize($cart->name));
            for ($i=0;$i<count($items);$i++){
                $items[$i]['project']=$project_name;
                if(isset($items[$i]['name'])){
                    $items[$i]['keyword'] = $items[$i]['name'];
                    unset($items[$i]['name']);
                }
                if($items[$i]['keyword'] == $request->keyword){
                    unset($items[$i]);
                    $sign = 1;
                }

            }



            if($sign == 0){
                return 404;
            }
            $cart->update(['name'=>serialize($items)]);
            $carts = Bom::where('user_id',Auth::guard('user')->id())->where('status',0)->first()->carts;
            foreach ($carts as $cart){
                array_push($content,array_values(unserialize($cart->name)));
            }

            return $this->readCart($request);
        }else{

           $cart = Bom::where('user_id', Auth::guard('user')->id())->where('status',0)->first()->carts->where('project_id',0)->first();
            if(is_null($cart)){
                return 'cart not found';
            }
            $items = array_values(unserialize($cart->name));

            for ($i=0;$i<count($items);$i++){
                $items[$i]['project']= null;
                if(isset($items[$i]['name'])){
                    $items[$i]['keyword'] = $items[$i]['name'];
                    unset($items[$i]['name']);
                }
                if($items[$i]['keyword'] == $request->keyword){
                    $sign = 1;
                    unset($items[$i]);
                    $sign = 1;
                }

            }
            if($sign == 0){
                return 404;
            }
            $cart->update(['name'=>serialize($items)]);

            $carts = Bom::where('user_id',Auth::guard('user')->id())->where('status',0)->first()->carts;
            foreach ($carts as $cart){
                array_push($content,array_values(unserialize($cart->name)));
            }

//            return array_values(array_filter($content));
            return $this->readCart($request);
        }



    }
// Required Params => token,phone,address,city,province,post

    public function getAddress(Request $request){

        //      Updating user information Tel & Address
//       $user = DB::table('users')->where('id',Auth::guard('user')->id())->first();
//       if(!is_null($user)){
//
//           DB::table('addresses')->where('user_id',Auth::guard('user')->id())->update([]);
//
//       }else{
        $errors = [];
        $validator = Validator::make($request->all(),[
            'address' => 'required',
            'province' => 'required',
            'post' => 'required',
            'city' => 'required',
            'tag' => 'required',
        ]);

        if($validator->fails()){

            for($i=0 ;$i<count($validator->errors()->getMessages());$i++){
                array_push($errors,array_values($validator->errors()->getMessages())[$i][0]);
            }

            return $errors;
        }

        $address = DB::table('addresses')->where('user_id',Auth::guard('user')->id())->where('tag',$request->tag)->first();
        // checks for duplicated tags
        if(!is_null($address)){

            return 'duplicate tag found!';
        }
            $newAdress = new Address();
        $newAdress->address = $request->address;
        $newAdress->province = $request->province;
        $newAdress->post = $request->post;
        $newAdress->city = $request->city;
        $newAdress->tag = $request->tag;
        $newAdress->user_id = Auth::guard('user')->id();
        $newAdress->created_at = Carbon::now();
        $newAdress->save();
            DB::table('boms')->where('user_id',Auth::guard('user')->id())->where('status',0)->update(['address_id'=>$newAdress->id]);
           DB::table('users')->where('id', Auth::guard('user')->id())->update(['phone'=>$request->phone]);

//       }

        return 200;
    }
    /*
     *
     * gets users tag insted of address
     * Required params : tag , token
     *
     */
    public function sendAddressTag(Request $request){

        if(!isset($request->all()['tag'])){
            return 'send a tag!';
        }
        $tag = $request->tag;
        $address = DB::table('addresses')->where('user_id',Auth::guard('user')->id())->where('tag',$tag)->first();
        if(is_null($address)){
            return 'no address found for this tag';
        }else{
            DB::table('boms')->where('user_id',Auth::guard('user')->id())->where('status',0)->update(['address_id'=>$address->id]);
            return 200;
        }

    }
    /*
     * sends all tags
     */
    public function getAddressTag(Request $request){

        $addresses = DB::table('addresses')->where('user_id',Auth::guard('user')->id())->get()->toArray();
        if(count($addresses) == 0){
            return [];
        }
        $arr = [];
        foreach ($addresses as $key=>$address){

            $arr[$address->tag]= get_object_vars($address);
        }

        return ($arr);
    }


    // get all carts related to the user bom
    // Required Params => token
    // calculate total price and update bom price column
    // close bom state
    // add price to cart parts
    // Required Param => token

    public function orderBill(Request $request){


        $usercart = $this->readCart($request);
        $totalPrice = 0;
        $itemArr = [];
        // check if the requested quantity is available or not
        $resp = $this->availability();
        $carts = Bom::where([['user_id', Auth::guard('user')->id()],['status',0]])->first()->carts;
        for($i=0;$i<count($carts);$i++){
            $items = array_values(unserialize($carts[$i]->name));
            // check each item price in a loop
            for($t=0;$t<count($items);$t++){
                $price = get_object_vars(DB::table('commons')->where('manufacturer_part_number',$items[$t]['keyword'])->first())['unit_price'];
                $quantity = get_object_vars(DB::table('commons')->where('manufacturer_part_number',$items[$t]['keyword'])->first())['quantity_available'];
                $items[$t]['price'] = $price;
//                    DB::table('commons')->where('manufacturer_part_number',$items[$t]['keyword'])->update(['quantity_available'=>$quantity - $items[$t]['num'] ]);
                array_push($itemArr,$items[$t]);
                $totalPrice = $totalPrice + $price * $items[$t]['num'];
            }

            $carts[$i]->update(['name'=>serialize($itemArr)]);
            $itemArr = [];
        }
        Bom::where([['user_id', Auth::guard('user')->id()],['status',0]])->first()->update(['delivery'=>10000]);
        $order_number = Bom::where([['user_id', Auth::guard('user')->id()],['status',0]])->first()->order_number;
        $delivery = Bom::where([['user_id', Auth::guard('user')->id()],['status',0]])->first()->delivery;
        $query = Address::where('user_id', Auth::guard('user')->id())->orderBy('created_at','desc')->first();
        Cache::put('totalPrice'.Auth::guard('user')->id(),$totalPrice+$delivery,10);
        return ['message'=>$resp,'cart'=>$usercart,'price'=>$totalPrice,'number'=>$order_number,
        'delivery'=>$delivery,
        'address'=>$query->address,
        'city'=>$query->city,
        'province'=>$query->province,
    ];
    }
/*
 * process bom and redirects to payment gateway
 *
 * Required Params => token, totalPrice
 */
    public function confirm(Request $request){
        if(is_null(Bom::where([['user_id', Auth::guard('user')->id()],['status',0]])->first())){
            return  ['body'=>'سبد خرید پیش از این پردازش شده است',
            'code'=>404
            ] ;
        }
        $itemArr = [];
        $carts = Bom::where([['user_id', Auth::guard('user')->id()],['status',0]])->first()->carts;
        for($i=0;$i<count($carts);$i++){
            $items = array_values(unserialize($carts[$i]->name));
            // check each item price in a loop
            for($t=0;$t<count($items);$t++){
                $price = get_object_vars(DB::table('commons')->where('manufacturer_part_number',$items[$t]['keyword'])->first())['unit_price'];
                $quantity = get_object_vars(DB::table('commons')->where('manufacturer_part_number',$items[$t]['keyword'])->first())['quantity_available'];
                $items[$t]['price'] = $price;
                    DB::table('commons')->where('manufacturer_part_number',$items[$t]['keyword'])->update(['quantity_available'=>$quantity - $items[$t]['num'] ]);
            }

            $carts[$i]->update(['name'=>serialize($items)]);
            $items = [];
        }
        Bom::where([['user_id', Auth::guard('user')->id()],['status',0]])->first()->update(['price'=>Cache::get('totalPrice'.Auth::guard('user')->id())]);
//        Bom::where([['user_id', Auth::guard('user')->id()],['status',0]])->first()->update(['status'=>50]);

//        return  ['body'=>'ok',
//            'code'=>200
//        ] ;
        /*
        * redirect to gateway
        */
        $urls = new URls();
        return redirect($urls::$gate.'?'.'token='.$request->token);


    }
    protected function availability($num = null){
        $itemArr = [];
        $fault = [];
        $carts = Bom::where([['user_id', Auth::guard('user')->id()],['status',0]])->first()->carts;
        for($i=0;$i<count($carts);$i++) {
            $items = array_values(unserialize($carts[$i]->name));
            // check each item price in a loop
            for ($s = 0; $s < count($items); $s++) {
                if(array_key_exists($items[$s]['keyword'],$itemArr)){
                    $itemArr[$items[$s]['keyword']] = $itemArr[$items[$s]['keyword']] + $items[$s]['num'];
                }else{

                    $itemArr[$items[$s]['keyword']] = $items[$s]['num'];
                }
                $quantity = get_object_vars(DB::table('commons')->where('manufacturer_part_number', $items[$s]['keyword'])->first())['quantity_available'];
                // check if the requested quantity is available


                    Cache::put($items[$s]['keyword'],$quantity,1);

                    Cache::put($items[$s]['keyword'],Cache::get($items[$s]['keyword']) - $itemArr[$items[$s]['keyword']],1);



                if ( Cache::get($items[$s]['keyword']) < 0) {
    //      Search for the unsufficient part in the cart to reduce number of that part
                    $contents = unserialize($carts[$i]->name);

            for ($m =0 ; $m <count($contents) ; $m++){

                if($contents[$m]['keyword'] == $items[$s]['keyword']){

                    $contents[$m]['num'] =  $contents[$m]['num']  -  $num;
                    if($contents[$m]['num'] <= 0){
                        unset($contents[$m]);
                    }
                        $carts[$i]->update(['name'=>serialize($contents)]);
                }

            }
                    array_push($fault,'موجود نمی باشد' . ' ' . $items[$s]['keyword'] . ' ' . 'در حال حاضر این تعداد از');

                }
            }


        }

        for ($s = 0; $s < count($items); $s++) {
            Cache::forget($items[$s]['keyword']);
        }
        if(sizeof($fault) > 0){
            $fault = array_unique($fault);
            return [
                'body'=>$fault,
                'code'=>404
            ] ;
        }else{

            return [
                'body'=> 'ok',
                'code'=>200
            ] ;
        }

    }
// Calculate total BOM price for final confirmation
    public function price(){

        if(is_null(Bom::where([['user_id', Auth::guard('user')->id()],['status',0]])->first())){
            return ['body' => 'سبد خریدی ثبت نشده است',
                'code'=>404];
        }
        $totalPrice = 0;
        $itemArr = [];
        // check if the requested quantity is available or not
//        $resp = $this->availability();
//        if($resp != 200 ){
//            return $resp;
//        }
        $carts = Bom::where([['user_id', Auth::guard('user')->id()],['status',0]])->first()->carts;
        for($i=0;$i<count($carts);$i++){
            $items = array_values(unserialize($carts[$i]->name));
            // check each item price in a loop
            for($t=0;$t<count($items);$t++){
                $price = get_object_vars(DB::table('commons')->where('manufacturer_part_number',$items[$t]['keyword'])->first())['unit_price'];
                $items[$t]['price'] = $price;
                array_push($itemArr,$items[$t]);
                $totalPrice = $totalPrice + $price * $items[$t]['num'];
            }
        }
        $order_number = Bom::where([['user_id', Auth::guard('user')->id()],['status',0]])->first()->order_number;
        return ['price'=>$totalPrice,'number'=>$order_number];
    }
/*
 * Required Params => nothing
 * Send => user's bill of materials
 */
    public function getUserBom(){

        $boms = DB::table('boms')->where('user_id',Auth::guard('user')->id())->get();

        if(sizeof($boms) == 0){

            return '404';
        }else{

            foreach ($boms as $bom){

                $bom->created_at = Jalalian::forge($bom->created_at)->toString();
            }


            return $boms;
        }

    }
/*
 *     Required Params => token,order_number
 *      Send =>  user cart
 */
    public function getUserBill(Request $request){

        try{

            $carts = Bom::where('order_number',$request->order_number)->first()->carts;
            $bom = Bom::where('order_number',$request->order_number)->first();
        }catch (\ErrorException $exception){

            return 'ORDER NUMBER NOT FOUND!';
        }

//        $userCart = [];
        if($bom->status != 0 ){

            for ($t=0;$t<count($carts);$t++){
                if($carts[$t]['project_id'] != 0){
                    $temp = array_values(unserialize($carts[$t]->name));
                    $prjName = DB::table('projects')->where('id',$carts[$t]['project_id'])->first()->name;
                    for($i=0 ; $i<count($temp);$i++){

                        $temp[$i]['project'] = $prjName;
                    }
                    $userCart['cart'][$t] = $temp;
                }else{
                    $temp = array_values(unserialize($carts[$t]->name));
                    for($i=0 ; $i<count($temp);$i++){

                        $temp[$i]['project'] = null;
                    }
                    $userCart['cart'][$t] = $temp;

                }
            }

            $userCart['order_number'] = $bom->order_number;
            $userCart['date'] = Jalalian::forge($bom->updated_at)->toString();
            $userCart['totalPrice'] = $bom->price;
            return ($userCart);
        }else{

            return 'سفارشی ثبت نشده است';
        }


    }

    public function Excel_export(){
        return Excel::download(new CartsExport, 'carts.xlsx');
    }
// Gets token,name(file name),project and imports data to carts table
    public function Excel_import(Request $request){

//        put the excel file in storage/imports
//      Get the file name from user
//        Get project name
        Excel::import(new CartsImport($request->project),'/imports/carts.xlsx');

        return 200;

    }



}
