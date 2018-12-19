<?php

namespace App\Http\Controllers;

use App\Brief;
use App\Component;
use App\Product;
use App\SubCategory;
use App\Underlay;
use GuzzleHttp\Client as GuzzleClient;
use App\Image;
use App\Repository\Cropper;
use App\User;
use App\Variable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;
use Psr\Http\Message\ResponseInterface;

class PageController extends Controller
{
    public $info = 10;

    public function home($category=null,Request $request){

      $contents = Brief::orderBy('id','desc')->get()->take($this->info);
        if($category){
            $contents = Brief::orderBy('id','desc')->where('category',$category)->get()->take($this->info);
        }
        // else{
        //     if($dataId - $this->info < 0){
        //         $contents = Brief::where('id','<=',$dataId)->get();
        //     }else{

        //         $contents = Brief::where([['id','<',$dataId],['id','>',$dataId - $this->info]])->get();
        //     }
        // }


        return $contents;
    }
/*
 * Gets num (1,2,3,4) , category and return more contents
 */
    public function moreContent($category=null,Request $request){
        $num = $request->num;
        $contents = Brief::orderBy('id','desc')->skip($num*10)->take($this->info)->get();
        if($category){
            $contents = Brief::orderBy('id','desc')->where('category',$category)
            ->skip($num*10)->take($this->info)->get();
        }
        // $contents = Brief::where([['id','<=',($dataId - $this->info -($num -1)*10)],['id','>',$dataId - $this->info - $num*10]] )->get();
        return $contents;
    }



    public function viewer(){

        $users = User::whereNotNull('token')->get();

        foreach ($users as $user){

            if(JWTAuth::parseToken($user->token)->check() === false){
                $user->update(['token'=>null]);
            }
        }
        $tokens = User::where('token','!=',null)->get();

        return 'Online Users : '.count($tokens);
    }
// ---------------- For test ------------------
    public function crop(){

        return view('test');
    }

    public function post_crop(Request $request){
        if($request->file('image') != null){
            $time = time();
            $img = new Image();
            $img->image = $time.'.'.$request->file('image')->getClientOriginalExtension();
            $request->file('image')->move('files/images',$img->image);
            $img->type = 'brief';
            $img->save();
            Cropper::crop($request,$img,$size='100x100',$time);

            return redirect('crop');
        }
    }

    public function Videos(){

        $url = 'https://www.aparat.com/etc/api/videoByUser/username/sahandmg/';
        $curl = curl_init();
        curl_setopt_array($curl, array(
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_URL => $url
        ));
        $result = curl_exec($curl);
        curl_close($curl);
        $results = json_decode($result,true)['videobyuser'];

       $videos = array_splice($results,0, 4);
        $list = [];
       for ($i=0;$i<count($videos);$i++) {
           $list[$i] = ['title'=>$videos[$i]['title'], 'frame'=>$videos[$i]['frame']];
       }

       return $list;

    }

    public function moreVideos(Request $request){
//        $num = 1 2 3 ....

        $num = $request->num;
        $url = 'https://www.aparat.com/etc/api/videoByUser/username/sahandmg/perpage/1000';

        if(!Cache::has('AparatRes')){
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_URL => $url
            ));

            $result = curl_exec($curl);
            curl_close($curl);
            $results = json_decode($result,true)['videobyuser'];
//            TODO Clear this cache every 24 hours
            Cache::put('AparatRes',$results,1440);
        }else{
            $results = Cache::get('AparatRes');
        }

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $url
        ));
        $result = curl_exec($curl);
        curl_close($curl);
        $results = json_decode($result,true)['videobyuser'];

        $videos = array_splice($results,($num-1)*10,10);
        $list = [];
        for ($i=0;$i<count($videos);$i++) {
            $list[$i] = ['title'=>$videos[$i]['title'], 'frame'=>$videos[$i]['frame']];
        }

        return $list;

    }
    /*
     * Required Params => nothing
     * Sends array of prvince names
     */
    public function Province(){

        $path = public_path('storage/province.json');
        $contents = json_decode(file_get_contents($path),true);
        $province = [];

        for($i=0;$i<count($contents);$i++){

            array_push($province,$contents[$i]['name']);

        }

        return $province;
    }
/*
 * Required Params => province
 * Sends => cities of province
 */
    public function City(Request $request){

        $path = public_path('storage/province.json');
        $contents = json_decode(file_get_contents($path),true);
        $province = $request->province;
        $cities = [];

        for($i=0;$i<count($contents);$i++){
            if($contents[$i]['name'] == $province){
                array_push($cities,$contents[$i]['Cities']);
            }

        }

        return $cities[0];
    }

    /*
     * Required Params => product
     */
    public function productMenu(Request $request){

        $product = $request->product;
        if(is_null($product)){

            return 'send a product name';
        }
        try{
            $product = str_replace(' ','_',$product);
            $components = Product::where('product_name',$product)->first()->subcategories->pluck('name')->toArray();
        }catch (\Exception $exception){

            return 'product not found';
        }

        return $components;
    }

    /*
    * Required Params => category
    */
    public function CategoryMenu(Request $request){

        $category = $request->category;
        if(is_null($category)){

            return 'send a category name';
        }
        try {
            $category = str_replace(' ', '_', $category);
            $components = SubCategory::where('name', $category)->first()->underlays()->get();
        }catch (\Exception $ex){

            return $ex;
        }

            /*
             * if subcategory doesn't have any subcategory
             * need to find the component_id of this subcategory and then
             * pluck all related records in commons
             */

            if(sizeof($components) == 0){
                try{
/*
 *  Finding parts from commons table
 */
        try{
            $categories =  SubCategory::where('name',$category)->first()->components->all();
        }catch (\Exception $exception){
            return $exception;
        }

                foreach ($categories as $item => $category){
                    $commons = DB::table('commons')->where('component_id',$category->id)->get()->toArray();
/*
 * Creating separate part model for fetching table data
 */
                    $rowModel = 'App\IC\\' . $category->name;
                    $instance = new $rowModel();
                    $table = $instance->getTable();
                    foreach ($commons as $key => $common){
                      $complete[$key] = DB::table('commons')->where('commons.id',$common->id)
                          ->join($table,$table.'.'.'common_id','=','commons.id')->get()->toArray();

                        unset($complete[$key][$item]->id);
                        unset($complete[$key][$item]->component_id);
                        unset($complete[$key][$item]->part_id);
                        unset($complete[$key][$item]->model);
                        unset($complete[$key][$item]->created_at);
                        unset($complete[$key][$item]->updated_at);
                        unset($complete[$key][$item]->common_id);
                        unset($complete[$key][$item]->product_id);
                    }
                }
//                 ---------------------------

                }catch (\Exception $exception){

                    return $exception;
                }

                return $complete;
            }else{

                $underlays = $components->pluck('name');
                for($i=0; $i<count($underlays); $i++){

                    $underlays[$i] = substr($underlays[$i],0,strlen($underlays[$i])-4);
                    $underlays[$i] = str_replace('_',' ',$underlays[$i]);
                }
                return $underlays;
            }

    }
    /*
    * Required Params => subcategory
    */
    public function subCategoryMenu(Request $request)
    {


        $subcategory = $request->subcategory;
        try {
            $subcategory = str_replace(' ', '_', $subcategory);
            $underlay = Underlay::where('name', 'like', "%$subcategory%")->first();
        } catch (\Exception $ex) {

            return $ex;
        }
        $subcategories = DB::table('sub_categories')->where('id', $underlay->id)->get();

        foreach ($subcategories as $key => $subcategory) {

            $categories[$key] = SubCategory::where('name', $subcategory->name)->first()->components->all();
        }

        foreach ($categories as $item => $category) {

                $commons = DB::table('commons')->where('component_id', $category[$item]->id)->get()->toArray();
                /*
                 * Creating separate part model for fetching table data
                 */
                $rowModel = 'App\IC\\' . $category[$item]->name;
                $instance = new $rowModel();
                $table = $instance->getTable();
                foreach ($commons as $key => $common) {
                    $complete[$key] = DB::table('commons')->where('commons.id', $common->id)
                        ->join($table, $table . '.' . 'common_id', '=', 'commons.id')->get()->toArray();
                    dd($table);
                    unset($complete[$key][$item]->id);
                    unset($complete[$key][$item]->component_id);
                    unset($complete[$key][$item]->part_id);
                    unset($complete[$key][$item]->model);
                    unset($complete[$key][$item]->created_at);
                    unset($complete[$key][$item]->updated_at);
                    unset($complete[$key][$item]->common_id);
                    unset($complete[$key][$item]->product_id);
                }
            }

            dd($complete);

        }

}
