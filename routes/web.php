<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


use App\Common;
use App\Repository\ColumnCode;
use Carbon\Carbon;
use Illuminate\Http\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\DomCrawler\Crawler;
use Tymon\JWTAuth\Facades\JWTAuth;
use GuzzleHttp\Client as GuzzleClient;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\RequestException;


Route::get('excel-export','CartController@Excel_export');
Route::get('excel-import','CartController@Excel_import');


Route::get('url',function (){

$client = new GuzzleClient();
    $promise = $client->requestAsync('POST', 'https://eshop.eca.ir/317-%D9%85%D9%82%D8%A7%D9%88%D9%85%D8%AA-%D9%87%D8%A7%DB%8C-%D8%AF%D9%82%DB%8C%D9%82-1-2-%D8%AF%D8%B1%D8%B5%D8%AF');
    $promise->then(
        function (ResponseInterface $res) {
            echo $res->getStatusCode() . "\n";
        },
        function (RequestException $e) {
            echo $e->getMessage() . "\n";
            echo $e->getRequest()->getMethod();
        }
    );
});

Route::get('page',function (){

    DB::table('commons')->update(['unit_price' => 0]);
    return view('test');
})->name('page');


Route::post('store',function (\Illuminate\Http\Request $request){

    $request->file('image')->move(storage_path('app/public/photos'),$request->file('image')->getClientOriginalName());
//    Storage::putFileAs('photos', new File(public_path('image.jpg')), 'image.jpg');

    return redirect()->route('page');

})->name('addImage');

Route::get('volts',function (){

    $datas = App\IC\Linear_Amplifiers_Audio::orderBy('voltage_supply','asc')->get();

    dd($datas->pluck('voltage_supply'));
});
/**
 *  Filling Common Table
 *  Put the csv file in public/comps directory
 */

Route::get('add-common',function (){

    $startMemory = memory_get_usage();


});


Route::get('do',function (){
    $filters = [

//        'input_type' => ['Single Ended'],
        'packaging'=>['Tube  Alternate Packaging'],
        'manufacturer'=>['Cirrus Logic Inc.'],
//        'voltage_supply_digital' => ['2 V ~ 5.5 V']

    ];
    $component = 'Data-Acquisition-Analog-to-Digital-Converters-ADC';
    $component = DB::table('components')->where('slug','like',"%$component%")->first();
    if($component == null ){
        return 410;
    }
//        Create class path from class string name --> App\IC\PMIC_Display_Drivers
    $class = 'App\IC\\'.str_replace('-','_',$component->slug);
//        Class Name --> PMIC_Display_Drivers
    $className = str_replace('-','_',$component->slug);
    $model = new $class();
    $commonTableCols = Schema::getColumnListing('commons');

//        Gets related model table name --> create__pmic_display_drivers__table
    $sepTableCols = Schema::getColumnListing($model->getTable());

    $common = DB::table('commons')->get();

    $separate = DB::table($model->getTable())->get();
    $cFlag = [];
    $sFlag = [];
//    $result = [];
//    $ids = [];


    for($i=0 ; $i < count($commonTableCols) ; $i++){
        for($t=0 ; $t<count($filters);$t++) {
//  Checking filter keys with common table column names to findout whether the common table needs to be filtered or not
            similar_text(array_keys($filters)[$t], $commonTableCols[$i], $percent);
            if ($percent >= 80) {
                array_push($cFlag, array_keys($filters)[$t]);
                $cFlag = array_unique($cFlag);
                $cFlag = array_values($cFlag);
            }
        }
    }
    for($i=0 ; $i < count($sepTableCols) ; $i++){
        for($t=0 ; $t<count($filters);$t++) {
//  Checking filter keys with separate tables column names to findout whether the tables need to be filtered or not
            similar_text(array_keys($filters)[$t], $sepTableCols[$i], $percent);
            if($percent >= 80){
                array_push($sFlag, array_keys($filters)[$t]);
                $sFlag = array_unique($sFlag);
                $sFlag = array_values($sFlag);
            }
        }
    }

    if($cFlag){
        for($i=0;$i<count($cFlag);$i++) {

            if(count($filters[$cFlag[$i]]) > 1) {
                for ($j = 0; $j < count($filters[$cFlag[$i]]); $j++) {

                    if ($j == count($filters[$cFlag[$i]]) - 1) {
                        break;
                    }

                    $common = $common->whereIn($cFlag[$i], [$filters[$cFlag[$i]][$j], $filters[$cFlag[$i]][$j + 1]])
                                     ->where('model',str_replace('-',' ',$component->slug));

                }
            }else{

//                for($i=0;$i<count($cFlag);$i++) {
                for ($j = 0; $j < count($filters[$cFlag[$i]]); $j++) {


                    $common = $common->where($cFlag[$i], $filters[$cFlag[$i]][$j])->where('model',str_replace('-',' ',$component->slug));

                }

            }
        }
        $common = array_values($common->all());

        for($i=0;$i<count($common);$i++){

            $ids[$i] = $common[$i]->id;

        }
    }

    if($sFlag){
        for($i=0;$i<count($sFlag);$i++) {

            if(count($filters[$sFlag[$i]]) > 1){

                for ($j = 0; $j < count($filters[$sFlag[$i]]); $j++) {

                    if ($j == count($filters[$sFlag[$i]]) - 1) {
                        break;
                    }
                    $separate = $separate->whereIn($sFlag[$i], [$filters[$sFlag[$i]][$j],$filters[$sFlag[$i]][$j+1]]);
                }
            }else{
                for ($j = 0; $j < count($filters[$sFlag[$i]]); $j++) {
                    $separate = $separate->where($sFlag[$i], $filters[$sFlag[$i]][$j]);
                }
            }

        }
        $separate = array_values($separate->all());

        for($i=0;$i<count($separate);$i++){

            $result[$i] = DB::table('commons')->where('id',$separate[$i]->common_id)->first()->id;

        }
    }

    if (isset($ids)  && isset($result)) {
        $sameIds = array_intersect($result, $ids);

    }elseif(isset($ids) && $sFlag == null){

        $sameIds = $ids;
    }elseif(isset($result) && $cFlag == null){
        $sameIds = $result;
    }else{

        return 415;
    }
    $sameIds = array_values($sameIds);

    for ($i = 0; $i < count($sameIds); $i++) {
//                $parts[$i] = DB::table('commons')->join('audio__special__purposes', 'commons.id', '=', 'audio__special__purposes.common_id')->get();
        $parts[$i] = Common::where('id', $sameIds[$i])->with($className)->first();
    }
    if (!isset($parts)) {
        return '415';
    } else {

//ساخت مجدد محتوای فیلترها بر اساس نتیجه جستجو شده

        $commonTableCols = Schema::getColumnListing('commons');

        $sepTableCols = Schema::getColumnListing($model->getTable());

        array_shift($sepTableCols);
        array_pop($sepTableCols);
        array_pop($sepTableCols);
        array_pop($sepTableCols);
        array_pop($sepTableCols);
        array_shift($commonTableCols);
        array_shift($commonTableCols);
        array_shift($commonTableCols);
        array_shift($commonTableCols);
        array_shift($commonTableCols);
        array_pop($commonTableCols);
        array_pop($commonTableCols);
        array_pop($commonTableCols);
        array_pop($commonTableCols);
        array_pop($commonTableCols);

        for ($t = 0; $t < count($commonTableCols); $t++) {
            for ($i = 0; $i < count($parts); $i++) {
                $cols[$commonTableCols[$t]][$i] = $parts[$i][$commonTableCols[$t]];
                $cols[$commonTableCols[$t]] = array_unique($cols[$commonTableCols[$t]]);
                $cols[$commonTableCols[$t]] = array_values($cols[$commonTableCols[$t]]);
            }
        }

        for ($t = 0; $t < count($sepTableCols); $t++) {
            for ($i = 0; $i < count($parts); $i++) {
                    $sepCols[$sepTableCols[$t]][$i] = $parts[$i][$className][$sepTableCols[$t]];
                    $sepCols[$sepTableCols[$t]] = array_unique($sepCols[$sepTableCols[$t]]);
                    $sepCols[$sepTableCols[$t]] = array_values($sepCols[$sepTableCols[$t]]);
            }
        }


        if (!isset($cols) || !isset($sepCols)) {
            return '420';
        } else {

            $result = array_merge($parts,$cols,$sepCols);
            return $result;
        }


    }











});
/**
 *  Filling Separates Table
 *  Put the ...2.csv files in public/comps directory
 */

Route::get('add-separate',function (){

    $startMemory = memory_get_usage();




});

/*
 * Modifying Helper table
 */
Route::get('helper-modifier',function (){

$names = \App\Helper::pluck('helper')->all();

    for ($i=0 ; $i<count($names);$i++){
        $name[$i] = unserialize($names[$i]);
        for($t = 0 ;$t<count($name[$i]) ; $t++){
            $name[$i][$t] = str_replace('__','_',$name[$i][$t]);
            $name[$i][$t] = str_replace(',','',$name[$i][$t]);
        }
        \App\Helper::find($i+1)->update(['helper'=>serialize($name[$i])]);
    }
    dd($name);


});

Route::get('/', function () {
   return view('welcome');
});

Route::get('login',function (\Illuminate\Http\Request $request){
    $shopResp = 0;
    Log::info("Searching for $request->keyword price ...");
    $start = Carbon::now();
    $partClass = Common::where('manufacturer_part_number','like',"%$request->keyword%")->get();
    $parts = $partClass->pluck('manufacturer_part_number');
    Log::info($parts);
    for($i=0;$i<count($parts);$i++) {
        $stop = 0;
        $command = "cd storage/V1 && node index.js $parts[$i] ";

        while ($stop == 0) {

            exec($command, $output, $return);
            if (count($output) != 0) {
                $stop = 1;
            } elseif (Carbon::now()->diffInSeconds($start) > 5) {
                $shopResp = '435';
                Log::warning('Get price status:' . $parts[$i].' --> '.'435 ' .' search stopped ...');
                $stop = 1;
            }
        }
        dd($output);
        if($shopResp != '435') {


            if (isset($output) && $output[0] != 'not found') {


                if (count($parts) == 0) {

                    $shopResp = '415';
                    Log::warning("Get price status: $parts[$i]" . ' --> ' . '415');
                }
                $partClass[$i]->update(['unit_price'=>$output[0]]);
                Log::warning("Get price status: $parts[$i] --> 200");
            } elseif (isset($output) && $output[0] == 'not found') {

                $shopResp = $parts[$i] . ' --> ' . '440';
                Log::warning('Get price status:' . $parts[$i] . ' --> ' . '440');
            } else {
                $shopResp = $output[0];
                Log::warning("Get price status: $output[0]");
            }
        }
//
//////                ------------------------------------------------
    }
//    return view('test');
});


Route::get('products',function (){

    $product = \App\Products::where('product_name','integrated circuits')->first();
    $components = $product->components;
    $i = 0;
    $t=0;
    foreach ($components as $component){
        $part[$i] = $component->commons;



        foreach ($part[$i] as $audio){

            $section[$t] = $audio->audios;
            $t++;
        }
        $i++;

    }
    return $section;
});

Route::get('token',function (){

    $startMemory = memory_get_usage();
    echo $startMemory.'<br>';
    $array = range(1, 100000);
    echo memory_get_usage() - $startMemory, ' bytes';
});

Route::get('get-auth',function (Request $request){


    $user = \App\User::find(2);
    $token = JWTAuth::fromUser($user);
    $user->update(['token'=>$token]);


});

Route::get('eager',function (){

   try{
       App\Brief::chunk(100,function ($briefs){

           foreach ($briefs as $brief){

               $brief->update(['title'=>'coding Best']);
           }
       });

   }
   catch (Exception $exception){
       dd($exception);

   }
    return \App\Brief::take(100)->get();

//
//    $briefs = \App\Brief::all();
//    foreach ($briefs as $brief){
////
//            $brief->update(['title'=>'coding war']);
//        }
//
// return \App\Brief::with('detail')->get();
//
});
Route::get('test',function (){


});
Route::get('login/google',['uses'=>'UserController@redirectToProvider'])->name('googleLogin');
//Route::get('login/google/callback',['uses'=>'AuthController@handleProviderCallback'])->name('googleCallback');

Route::get('crop','PageController@crop')->name('send');
Route::post('crop','PageController@post_crop')->name('send');
Route::get('run',function (\Illuminate\Http\Request $request){

    $rel = new App\Product();
    $keys = array_keys($rel->relationships());
    $part = App\Product::where('product_name','like',"%$request->keyword%")->with($keys)->get();

    if(count($part) == 0){

        $rel = new App\Component();
        $keys = array_keys($rel->relationships());
        $part = App\Component::where('name','like',"%$request->keyword%")->with($keys)->get();

    }

    if(count($part) == 0){

        $rel = new App\Common();
        $keys = array_keys($rel->relationships());
        if(array_search('component',$keys) !== false){
            $pos = array_search('component',$keys);
            unset($keys[$pos]);
            array_push($keys,'component.product');
            $part = App\Common::where('pn','like',"%$request->keyword%")
                ->orWhere('manufacturer_pn','like',"%$request->keyword%")
                ->orWhere('manufacturer','like',"%$request->keyword%")
                ->orWhere('description','like',"%$request->keyword%")->with($keys)->get();
        };
    }

    return $part;
//    return view('test',compact('part'));
});

/*
 * Runs php artisan make:model .... -m
 */

Route::get('make-table',function (){


});


Route::get('get-model-name',function (){


    $arr = App\Component::where('product_id',38)->get()->pluck('slug');
    $arr = str_replace('-','_',$arr);
    return $arr;

});
/*
 * Copies ld_image Column to datasheet Column
 */
Route::get('datasheet',function (){

    $images = DB::table('commons')->select('ld_image')->get();

    for($id=1;$id<=count($images);$id++){

        DB::table('commons')->where('id',$id)->update(['datasheet'=>$images[$id-1]->ld_image]);
    }

    return 200;

});

/*
 * Copies manufacturer_part_number Column to ld_image Column
 */

Route::get('ldimage',function (){

    $pns = DB::table('commons')->orderBy('id')->select('manufacturer_part_number')->get();

    for($id=3001;$id<=3907;$id++){

        DB::table('commons')->where('id',$id)->update(['ld_image'=>$pns[$id-1]->manufacturer_part_number]);
    }

    return 200;
});
/*
 * This routes are using for getting all column names and
 * creating a unit array of names with codes map to them
 * for sending as query string in filter url
 */

App::instance('ColumnCode',new ColumnCode());
Route::get('column/name','TableController@ColumnName');
Route::get('column/merge','TableController@merging');
Route::get('column/map','TableController@mapping');
Route::get('getLinks','TableMakerController@getLinks');
Route::get('import-category','TableMakerController@import_Categories');
Route::get('migration','TableMakerController@migration_maker');
Route::get('subcategory','TableMakerController@subcategory');
Route::get('category','TableMakerController@category');
Route::get('underlay','TableMakerController@underlay');
Route::get('products','TableMakerController@products');
Route::get('fill-table','TableMakerController@FillTable');
Route::get('commons','TableMakerController@commons');
Route::get('separate','TableMakerController@separate');
Route::get('mycomponent','TableMakerController@component');
Route::get('modify-id','TableMakerController@modifyId');
Route::get('modify-commonid','TableMakerController@modifyCommonId');
Route::get('modify-table','TableMakerController@modifyTable');
Route::get('modify-table','TableMakerController@modifyTable');
Route::get('common-id','TableMakerController@CommonId');
Route::get('check-model','TableMakerController@checkModelNum');
Route::get('public-id','TableMakerController@modifyPublic');

