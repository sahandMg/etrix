<?php

namespace App\Http\Controllers;

use App\Common;
use App\Helper;
use App\PartMaping;
use App\Product;

use App\Underlay;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PartController extends Controller
{
    /* جدول مربوط به ستون ها جداول
     *  Adding Part to Database :
     *
     * 1. Write a function to get the commons table column data
     * 2. Write a function to get the separate table column data
     * 3. Add id of commons to common_id column of separate
     */
    public function makeJson(){

        $data = [];
        $set = [];

        $presetColumns = ['ld_image','hd_image','datasheet','footprint','manufacturer_part_number',
            'quantity_available','unit_price','manufacturer','description',
            'packaging','series','part_status','minimum_quantity','original',

        ];
        for($i=1 ; $i<= Product::count();$i++) {

//            try{
            for ($t = 0; $t < Product::find($i)->components->count(); $t++) {

                $product = Product::find($i);
                $names = unserialize(Helper::where('id',$product->components[$t]->id)->first()->helper);

                try{
                    $subcats = $product->components[$t]->subcategory->name;
                    $underlay = $product->components[$t]->underlay->name;
                }catch (\Exception $exception){
                    $underlay = null;
                }
                if(is_null($underlay)){

                    $data = [
                        'category' => [$product->product_name, $subcats],
                        'component_id' => $product->components[$t]->id,
                        'product_id' => $product->id,
                        'columns' => array_merge($presetColumns,$names)
                    ];
                    array_push($set,$data);
                }else{

                    $underlay = substr($underlay,0,-4);
                    $data = [
                        'category' => [$product->product_name, $subcats,$underlay],
                        'component_id' => $product->components[$t]->id,
                        'product_id' => $product->id,
                        'columns' => array_merge($presetColumns,$names)
                    ];

                    array_push($set,$data);
                }

            }

//            }catch (\Exception $exception){

//                return [$exception,$i];
//            }
        }

        return $set;

    }

    /*
     * These methods add new parts data to commons and separate table
     */

    /*
     * Gets all data as json { commons }
     */
    public function addNewParts(Request $request){

        $request = $request->all();
        if(!isset($request['commons'])){

            return 'commons not set!';
        }
        if(!isset($request['separate'])){

            return 'separate not set!';
        }

        $commons = $request['commons'];
        $separates = $request['separate'];
        if(isset($request['website'])){
            $mapping = new PartMaping();
            $mapping->website = $request['website'];
            $mapping->crawled_name = $commons['manufacturer_part_number'];
            $mapping->map_name = $request['map_name'];
            $mapping->save();

        }
        $dbCommons = new Common();

        foreach ($commons as $key=>$common){

            $part = DB::table('commons')->where('manufacturer_part_number',$commons['manufacturer_part_number'])->first();
            if(is_null($part)){

                $dbCommons->$key = $common;
            }else{
                return 'Duplicate Part data!';
            }
        }
        // counts how many parts are in a separate table related to this component to calculate part_id number
        $partNumber = DB::table('commons')->where('component_id',$dbCommons->component_id)->count();
        $dbCommons->part_id = $partNumber + 1;

        $dbCommons->save();
        try{

            $componentName = DB::table('components')->where('id',$dbCommons->component_id)->first()->name;
        }catch (\Exception $ex){
            return 'not valid in components';
        }
        $model = 'App\IC\\'.$componentName;
        $dbSeparate = new $model;

        foreach ($separates as $key=>$separate){

            $dbSeparate->$key = $separate;
        }
        $dbSeparate->common_id = $dbCommons->id;

//        dd($dbSeparate,$dbCommons);
        $dbSeparate->save();


        return 200;
    }
    /*
     * Add new Parts Manually buy using subcategory name
     * returns column names
     */
    public function addNewPartsManual(Request $request){

        $category = $request->category;
        $subcategory = str_replace('*','',strrchr($category,'*'));
        $subcategory = str_replace(' ','_',$subcategory);
        $str = 'App\IC\\'.$subcategory;
        try{
            $model = new $str();
        }catch (\Exception $exception){
            return 'No model found for '.$str.' at line '.$exception->getLine();
        }
        try{

            $component = DB::table('components')->where('name',$subcategory)->first();
            $component_id = $component->id;
            $product_id = $component->product_id;
        }catch (\Exception $exception){

            return 'component not found for '.$subcategory;
        }
        $privateColumns = Schema::getColumnListing($model->getTable());
        array_pop($privateColumns);
        array_pop($privateColumns);
        array_shift($privateColumns);
        $common = new Common();
        $commonColumns = Schema::getColumnListing($common->getTable());
        array_pop($commonColumns);
        array_pop($commonColumns);
        array_pop($commonColumns);
        array_pop($commonColumns);
        array_shift($commonColumns);
//        $partArray = array_merge($commonColumns,$privateColumns);
        $privateColumnNames = [];
        $commonColumnNames = [];
        for($i=0;$i<count($commonColumns);$i++){
            $commonColumnNames[$commonColumns[$i]] = '';
            if($commonColumns[$i] == 'component_id' ){
                $commonColumnNames[$commonColumns[$i]] = $component_id;
            }

        }

        for($i=0;$i<count($privateColumns);$i++){
            $privateColumnNames[$privateColumns[$i]] = '';
            if($privateColumns[$i] == 'product_id' ){
                $privateColumnNames[$privateColumns[$i]] = $product_id;
            }
        }


        return ['commons'=>$commonColumnNames,'separate'=>$privateColumnNames];


    }
}
