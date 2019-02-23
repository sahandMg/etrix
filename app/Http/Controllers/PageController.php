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
use Maatwebsite\Excel\Facades\Excel;
use Stichoza\GoogleTranslate\GoogleTranslate;
use App\User;
use App\Variable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Stevebauman\Location\Facades\Location;
use Tymon\JWTAuth\Facades\JWTAuth;
use Psr\Http\Message\ResponseInterface;

class PageController extends Controller
{
    public $info = 10;

    public function test(){
        $userCartDatas = DB::table('carts')->where('bom_id',33)->get();
//
//        foreach ($userCartDatas as $key => $userCartData){
//
//            $data[$key] = unserialize($userCartData->name);
//        }
//        return view('emails.cart',compact('data'));

        dd(Cache::get('translated'));
    }
    public function checkMe($code=null){

        if(is_null($code)){
            return 'null code';
        }else{

          $randomNumber = $code;
         $cipher = "aes-128-gcm";
         $key1 = "sahand";
         $key2 = "mohammad";
        if (in_array($cipher, openssl_get_cipher_methods()))
         {
          $ivlen = openssl_cipher_iv_length($cipher);
          $ciphertext = openssl_encrypt($randomNumber, $cipher, $key1, $options=0, $key1, $tag);
          $ciphertext = preg_replace("/[^a-zA-Z0-9]+/", "", $ciphertext);
          $ciphertext = strtolower($ciphertext);
          $ciphertext2 = openssl_encrypt($randomNumber, $cipher, $key2, $options=0, $key2, $tag);
          $ciphertext2 = preg_replace("/[^a-zA-Z0-9]+/", "", $ciphertext2);
          $ciphertext2 = strtolower($ciphertext2);
                return ['key'=>$randomNumber,'trial'=>$ciphertext,'original'=>$ciphertext2];

            }

        

  }

    }


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

        $path = public_path('files/province.json');
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

        $path = public_path('files/province.json');
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

    public function translate(){
        Cache::forget('translated');
        $nameArray = [];
        $tr = new GoogleTranslate('en');
        $columnArrays = DB::table('helpers')->get()->pluck('helper');
        foreach ($columnArrays as $key => $columnArray){

            $columnArrayU = unserialize($columnArray);

            for($i = 0 ; $i < count($columnArrayU); $i ++){
//                $nameArray[$columnArrayU[$i]] =  $tr->setSource('en')->setTarget('fa')->translate(str_replace('_',' ',$columnArrayU[$i]));
                $nameArray[$columnArrayU[$i]] =  str_replace('_',' ',$columnArrayU[$i]);

            }
        }

        return array_keys($nameArray);


    }



}
