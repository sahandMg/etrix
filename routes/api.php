<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//Route::middleware('auth:api')->get('/user', function (Request $request) {
//    return $request->user();
//});
use App\Common;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

Route::get('test',function (){

//    $stop = 0;
//    $parts = [];
//    $this->keyword = '10uf';
//    $start = Carbon::now();
//    $partClass = Common::where('manufacturer_part_number','like',"%$this->keyword%")->get();
//    $componentIds = $partClass->pluck('component_id');
//    foreach($componentIds as $componentId){
//        $productId = DB::table('components')->where('id',$componentId)->first()->product_id;
//
//        if($productId == 4 || $productId == 26){
//            $recapsTable = 1;
//        }
//    }
//    if(isset($recapsTable)){
//        $recaps = DB::table('re_caps')->where('value','like',"%$this->keyword%")->get();
//        $parts = $recaps->pluck('name');
//    }else{
//
//        $parts = $partClass->pluck('manufacturer_part_number');
//    }
//
//
//    Log::info($parts);
//
//    for($i=0;$i<count($parts);$i++) {
//        $output =[];
//        $stop = 0;
//        $path = public_path('storage/V1');
//        $command = "cd $path && node index.js $parts[$i]";
//
//        Log::info("Searching for $parts[$i] ...");
//        while ($stop == 0) {
//
//            exec($command, $output, $return);
//
//            if (count($output) != 0) {
//                $stop = 1;
//
//            } elseif ($start->diffInSeconds(Carbon::now()) > 50) {
//                $this->shopResp = '435';
//                Log::warning("Get price status: $parts[$i]".' --> '.'435 ' .' search stopped ...');
//                $stop = 1;
//            }
//        }
//
//        if($this->shopResp != '435') {
//
//            Log::warning($output[0]);
//            if (isset($output) && $output[0] != 'not found') {
//
//
//                if (count($parts) == 0) {
//
//                    $this->shopResp = '415';
//                    Log::warning('Get price status:' . $parts[$i] . ' --> ' . '415');
//                }
//                $arr = explode(',',$output[0]);
//                $price = $arr[0];
//                if(isset($arr[1])&& sizeof($arr) > 0){
//
//                    $quantity = ceil($arr[1]/2);
//                    $partClass[$i]->update(['quantity_available'=>$quantity]);
//                }
//                $partClass[$i]->update(['unit_price'=>$price]);
//
//                Log::warning("Get price status: 200");
//            } elseif (isset($output) && $output[0] == 'not found') {
//
//                $this->shopResp = $parts[$i] . ' --> ' . '404';
//                Log::warning('Get price status:' . $parts[$i] . ' --> ' . '404');
//            } else {
//                $this->shopResp = $output[0];
//                Log::warning("Get price status: $output[0]");
//            }
//        }
////
////////                ------------------------------------------------
//    }
    \App\Jobs\GetPrice::dispatch('ne555');
Log::info('Searching ?');
});
Route::post('/',function (){

});

Route::post('more-content/{category?}','PageController@moreContent');
Route::get('videos','PageController@Videos');
Route::get('more-videos','PageController@moreVideos');
Route::post('home/{category?}','PageController@home');
Route::get('excel-export','CartController@Excel_export');
Route::get('excel-import','CartController@Excel_import');
Route::get('province','PageController@Province');
Route::get('city','PageController@City');


// ----------------------------- User Routes ----------------------------------------
//[

    Route::group(['prefix'=>'user'],function(){

        Route::post('register','UserController@register');
        Route::post('login','UserController@login');
        Route::post('data','UserController@userData');
        Route::get('login/google/callback',['uses'=>'UserController@handleProviderCallback']);
        Route::post('bom','CartController@getUserBom')->name('getUserBom');
        Route::post('bill','CartController@getUserBill')->name('getUserBill');

        Route::group(['prefix'=>'cart'],function (){

            Route::post('create','CartController@createCart');
            Route::post('read','CartController@readCart');
            Route::post('edit','CartController@editCart');
            Route::get('excel','CartController@Excel');

            Route::post('add','CartController@addToCart');
            Route::post('confirm','CartController@confirm');
            Route::post('order-bill','CartController@orderBill');
            Route::post('price','CartController@price');
            Route::post('address','CartController@getAddress');


        });


        Route::group(['prefix'=>'project'],function (){

            Route::post('create','ProjectController@createProject');
            Route::post('read','ProjectController@readProject');
            Route::post('detail','ProjectController@detail');
            Route::post('delete','ProjectController@deleteProject');
        });

    });

//]
// --------------------------------- Content Manager Routes ------------------------------------
// [
    Route::group(['prefix'=>'cm'],function(){
        Route::post('login','CmController@login');
        Route::post('content/add','CmController@addContent');
        Route::post('content/edit','CmController@editContent');
        Route::post('content/get','CmController@getContent');
        Route::post('image/add','CmController@addImage')->name('addImage');
        Route::get('image/get','CmController@getImages');
        /**
         */
//        ->middleware('terminate')
        Route::get('login/google',['uses'=>'CmController@redirectToProvider'])->name('googleLogin');
        Route::get('login/google/callback',['uses'=>'CmController@handleProviderCallback']);


    });
// ]
// ----------------------------- Admin Routes ----------------------------------------
// [
    Route::group(['prefix'=>'admin'],function(){
        Route::post('cm/register','AdminController@registerCm');
        Route::post('register','AdminController@register');
        Route::post('cm/delete','AdminController@deleteCm')->middleware('terminate');
        Route::post('control-panel','AdminController@controlPanel');
        Route::post('login','AdminController@login');
});

// ]


// ---------------------------------------------------------------------
Route::get('add-slug','ProductController@addSlug');
Route::get('get-products','ProductController@all');

Route::post('logout','AuthController@logout')->name('logout');

// -------------------------------  Searching without filter  -----------------------------------
// [
    Route::get('search-part-comp','SearchController@SearchPartComp');
    Route::get('search-part','SearchController@SearchPart');
    Route::get('search-article','SearchController@findArticle');
    Route::post('sort-col','SearchController@sort');
    Route::get('subMenu','SearchController@subMenu');
    // -------------------------------  Getting price from shops  -----------------------------------
    Route::post('get-price','SearchController@getPrice');

// -------------------------------  Getting price from shops  -----------------------------------
    Route::post('get-quantity','SearchController@getQuantity');
    // -------------------------------  Searching with filter  -----------------------------------

    Route::get('search-part-filter','SearchController@filterPart');

    Route::get('product-menu','SearchController@productMenu');
    Route::get('category-menu','SearchController@CategoryMenu');
    Route::get('subcategory-menu','SearchController@subCategoryMenu');

//]
// -------------------------------  Site Viewers  -----------------------------------
Route::get('get-viewer','PageController@viewer');

// -------------------------------  Add/Read/Edit Parts Routes  -----------------------------------
//[

    Route::post('edit-part','ProductController@edit');

    Route::post('get-part-list','ProductController@getPartList');

// get all parts from a component type like Audio Special Purpose
    Route::post('get-part','ProductController@getPart');
//    Route::post('create/guest','CartController@cartWithoutToken');
//]
