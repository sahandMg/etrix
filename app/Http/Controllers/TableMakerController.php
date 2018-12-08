<?php

namespace App\Http\Controllers;

use App\Category;
use App\Comment;
use App\Common;
use App\Component;
use App\Helper;
use App\Imports\CommonsImport;
use App\Product;
use App\SubCategory;
use App\Underlay;
use Dotenv\Exception\InvalidFileException;
use Illuminate\Http\Request;
use App\Shops\Digikey;
use App\imports\ComponentsImport;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use League\Flysystem\FileNotFoundException;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class TableMakerController extends Controller
{

    public $add = 0;
    public $key = 0;
 	public function getLinks(){


 		$digikey = new Digikey();
 		return $digikey->getLinks();

/*
 * Use this to reorder helpers table id
 */
//        $helpers = Helper::all();
//        foreach ($helpers as $key => $helper){
//            $helper->update(['id'=>$key+1]);
//        }
 	}
 	/*
	Import category.csv
	collect category names
	store them in components table
 	*/

 	public function import_Categories(){

 			Excel::import(new ComponentsImport,'/imports/categories_main.csv');
        return 200;

 	}
/*
 *  make table migration
 */
 	public function migration_maker(){

        $components = Component::orderBy('name','asc')->get()->pluck('name');

        for($i=7;$i<20;$i++){

            Artisan::call("make:model",['name'=>' IC/'.$components[$i],'-m'=>'--migration']);

        }
        return 200;

    }

    /*
     *  fills subcategories table
     *  EX: integrated_circuits->PMIC->A221xg  --> PMIC is subcategory
     */
    public function subcategory(){

//        Excel::import(new ComponentsImport,'/imports/categories.csv');
//        $cats = array_values(array_unique(Cache::get('subcategory')));
//        for($i=0;$i<count($cats);$i++){
//            $sub = new SubCategory();
//            $sub->name = $cats[$i];
//            $sub->save();
//        }


//      ---------------------===-------------------------------______-_----_-_--_-__-_--_______--__

    /*
     *  RUN THIS CODE TO REMOVE '_' CHARACTER FROM THE BEGINNING OF THE SUBCATEGORIES
     */


//        $components = SubCategory::all();
//        foreach ($components as $component){
//            if(substr($component->name,0,1) == '_'){
//                $characters = explode('_',$component->name);
//                unset($characters[0]);
//                $characters = implode("_",$characters);
//                $component->update(['name' => $characters]);
//            }
//            print_r('<pre>'.$component->name.'</pre>');
//        }
//
//        return 200;

    }

/*
 *  Fills categories table
 *  EX : Integrated Circuits (ICs) -> PMIC -> Motor Drivers_ Controllers ==> IC is a category == product
 *  Sooti dadam
 */
    public function category(){

        Excel::import(new ComponentsImport,'/imports/categories.csv');
        $cats = array_values(array_unique(Cache::get('category')));
        for($i=0;$i<count($cats);$i++){
            $sub = new Category();
            $sub->name = $cats[$i];
            $sub->save();
        }
    }

    public function underlay(){

//        Excel::import(new ComponentsImport,'/imports/categories.csv');
//        $cats = array_values(array_unique(Cache::get('underlay')));
//        for($i=0;$i<count($cats);$i++){
//            $sub = new Underlay();
//            $sub->name = $cats[$i];
//            $sub->save();
//        }

//        -------------------------------'--------------------------'-'--'-'--'-'-'-'-'---'-'------------
        /*
         *  RUN THIS CODE TO REMOVE '_' CHARACTER FROM THE BEGINNING OF THE SUBCATEGORIES
         */
//        -------------------------------'--------------------------'-'--'-'--'-'-'-'-'---'-'------------

//        $components = Underlay::all();
//        foreach ($components as $component){
//            if(substr($component->name,0,1) == '_'){
//                $characters = explode('_',$component->name);
//                unset($characters[0]);
//                $characters = implode("_",$characters);
//                $component->update(['name' => $characters]);
//            }
//            print_r('<pre>'.$component->name.'</pre>');
//        }

        return 200;

    }

    public function products(){

        Excel::import(new ComponentsImport,'/imports/categories.csv');
        $cats = array_values(array_unique(Cache::get('product')));

        for($i=0;$i<count($cats);$i++){
            $sub = new Product();
            $sub->product_name = $cats[$i];
            $sub->slug = $cats[$i];
            $sub->save();
        }
        return 200;
    }
    /*
     * Fills migrated table with column names
     * needs to change the migration class method dynamically
     */
    public function FillTable(){

        $helpers = Helper::all();

        foreach ($helpers as $key=>$helper){

            $helper->update(['id'=>$key+1]);
        }
    }

/*
 *  Fills commons table import csv file directly to phpmyadmin
 */
    public function commons(){


        $commons = DB::table('commons')->get();
        $arr = DB::table('commons')->get()->pluck('manufacturer_part_number')->toArray();
        $values = array_count_values($arr);
        foreach ($commons as $key => $common){

            if(array_search($common->manufacturer_part_number,$arr) != $key){

                DB::table('commons')->where('manufacturer_part_number',$common->manufacturer_part_number)->delete();
            }

//            DB::table('commons')->where('manufacturer_part_number',$common->manufacturer_part_number)->update(['id'=>$key+1]);

        }
        return 200;

    }

    public function separate(){
        ini_set('memory_limit', '1024M');
//        print_r(phpinfo());
        $models = DB::table('components')->get()->pluck('name');

            $path = "/imports/newCSVs_previously Added to CSV result";
            /*
             * Use this code to rename csv files (removing '_' from endpoints)
             */
//            $path = public_path("/imports/CSVResult");
//            $names = array_diff(scandir($path), array('.', '..'));
//            array_shift($names);
//            for($i=0 ; $i<count($names); $i++){
//                if(str_split($names[$i])[strlen($names[$i])-5] == '_'){
//
//                    rename($path.'/'.$names[$i],$path.'/'.mb_substr($names[$i],0,-5).'.csv');
//                    $names[$i] = mb_substr($names[$i],0,-5);
//                }
//            }

//            ---------------------------------------------------------------------------------

            for($i= 45 ;$i< 46; $i++){
                try{
/*
 *         Integrated_Circuits_ICs_
 *          Isolators_
 *          Discrete_Semiconductor_
*/
                Excel::import(new CommonsImport($models[$i]),$path."/$models[$i].csv");
                }catch (FileNotFoundException $ex){

                dd($ex);

                }

//                fclose($file);
            }

             dd(200);

    }

    public function component(){
        Component::where('id','>',72)->where('id','<',168)->chunk(20,function ($vars){

                foreach ($vars as $var){
                    $var->update(['product_id'=>15]);
                }
        });
    }
    /*
     * Reordering ids in separate tables
     */
    public function modifyId(){
        ini_set('memory_limit', '1024M');
            $components = DB::table('components')->get()->pluck('name');
            foreach ($components as $key => $component) {
                   $myModel = 'App\IC\\'.$component;
                    $instance = new $myModel();
                    try {
                        $tableName = $instance->getTable();

                        session()->put('table', $tableName);
                        if (count(DB::table($tableName)->get()) > 0) {
                            $tableName = session('table');

                            DB::table($tableName)->orderBy('id')->chunkById(1600, function ($queries) {
                                foreach ($queries as $key => $query) {
                                    $tableName = session('table');
                                    DB::table($tableName)->where('id', $query->id)->update(['id' => $key + 1]);
                                }
                            });
                            echo $tableName;
                        }
                    }catch (\Exception $exception){

                    }

            }

    }
/*
 * Reordering ids in commons table
 */
    public function modifyCommonId(){
        ini_set('memory_limit', '1024M');

                $num = 100;


                    DB::table('commons')->orderBy('id')->chunkById($num, function ($queries) use($num) {
                        foreach ($queries as $key => $query) {

                            DB::table('commons')->where('id', $query->id)->update(['id' => $this->add + $key + 1]);
                            if(fmod($key,100) == 99 ){
                                $this->add = $this->add + 100;
                            }
                        }
                    });
                }
/*
 * This method compare number of excel rows with the peer table rows in database and ...
 *  defines tables that have extra or duplicated rows
 *  Get model names form models table
 * Get proper csv file and get its row number
 * Get the row number of the related table of the model
 * Compare this two numbers
 */
    public function  modifyTable(){

        $names = DB::table('models')->get()->pluck('model');
        $secNames = DB::table('components')->get()->pluck('name');
        $path = public_path('storage/imports/newCSVs_previously Added to CSV result');
        $corrupt = [];
        for($i=0;$i<count($names);$i++){
            try{
//                $reader = IOFactory::createReaderForFile($path."/$names[$i].csv");
//                $reader->setReadDataOnly(true);
//                $reader->load($path."/$names[$i].csv");
                $csv = new Csv();
                $csv->load($path."/$names[$i].csv");
            }catch (\InvalidArgumentException $exc){
                continue;
            }

            try{
                $model = 'App\IC\\'.$secNames[$i];
                $instance = new $model();
                $id = DB::table($instance->getTable())->orderBy('id','desc')->first()->id;
                $rowNum = $csv->load($path."/$names[$i].csv")->getActiveSheet()->getHighestDataRow();
                if($id != $rowNum){
                    array_push($corrupt,$names[$i]);
                }
            }catch (\Exception $exception){
                continue;
            }



        }

        return $corrupt;

    }

    /*
     * Edit common_id in separate tables
     * First choose a category then find its id in commons table and gets the related queries
     * From those queries, grab part_id and set them as common_id in the related category table
     */
    public function CommonId(){
        $modelNames = DB::table('components')->get()->pluck('name');
        for($i=1;$i<100;$i++){
            $this->add = 0;
            $model = 'App\IC\\'.$modelNames[$i];

            $instance = new $model();
            $id = DB::table('components')->where('name',$modelNames[$i])->first()->id;
//            dd($model);
            $queries = DB::table('commons')->where('component_id',$id)->get()->pluck('id');
//      count($queries) == count(DB::table($instance->getTable())->get())
            try{
                $separates = DB::table($instance->getTable())->orderBy('id')->chunkById(100,function ($datas) use($instance,$queries){
                    for ($t=0 ; $t<count($datas);$t++) {
                        $this->key = $t;
                        DB::table($instance->getTable())->where('id', $datas[$t]->id)->update(['common_id' => $queries[$this->add + $t]]);
                        if(fmod($t,100) == 99 ){
                            $this->add = $this->add + 100;
                        }


                    }
                });
            }
            catch (\Exception $exception){
                dd($exception,$i,$this->add,$this->key,count($queries));
            }




        }



        dd(200);
    }
    /*
     * Check number of specific components_id of commons table with the number of
     * parts inserted to the related model table
     */
    public function checkModelNum(){


        $corrupt = [];
        $names = DB::table('components')->get();
        for($i=0;$i<count($names);$i++){

            $model = 'App\IC\\'.$names[$i]->name;
            $instance = new $model();

            $row = DB::table($instance->getTable())->get();
            try{


        }catch (\Exception $exception){


            continue;
        }

            $row2 = DB::table('commons')->where('component_id',$names[$i]->id)->get();
            if(count($row) != count($row2)){
                if(count($row) > 0) {
                    $diff = abs(count($row2)-count($row));
                    $corrupt[$i] = $names[$i]->name.'  ==>  '.$diff;
                }
            }
        }
        dd($corrupt);


    }
    /*
     * Modifies product_id in separate tables
     * get all of the models with their corresponding table
     * get the component name for each table
     * get public_id from components table that matches our model
     */
    public function modifyPublic(){
//        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
        $modelNames = DB::table('components')->get()->pluck('name');
        try{

            for($i=56;$i<57;$i++){
                $this->add = 0;
                $model = 'App\IC\\'.$modelNames[$i];
                $instance = new $model();
                $id = DB::table('components')->where('name',$modelNames[$i])->first()->product_id;
                DB::table($instance->getTable())->chunkById(100,function($queries) use($instance,$id){

                    for ($t=0 ; $t<count($queries);$t++) {
                        $this->key = $t;
                        DB::table($instance->getTable())->where('id', $queries[$t]->id)->update(['product_id' => $id]);

                    }
                });

            }

        }catch (\Exception $exception){
            dd($exception);
        }
        return 200;
    }
    /*
     * Create codes for column names to use in filter part api
     */
    public function columnCode(){
        $columnNames = [];
        $names = [];
        $codes = [];
        $columns = DB::table('helpers')->pluck('helper');
        foreach ($columns as $key=>$column){
            $columnNames[$key] = unserialize($column);
        }
        for($i=0;$i<count($columnNames);$i++){
            for($j=0;$j<count($columnNames[$i]);$j++){

                array_push($names,$columnNames[$i][$j]);
            }

        }
        array_push($names,'hd_image');
        array_push($names,'ld_image');
        array_push($names,'datasheet');
        array_push($names,'footprint');
        array_push($names,'manufacturer_part_number');
        array_push($names,'quantity_available');
        array_push($names,'unit_price');
        array_push($names,'manufacturer');
        array_push($names,'description');
        array_push($names,'packaging');
        array_push($names,'series');
        array_push($names,'part_status');
        array_push($names,'minimum_quantity');
        array_push($names,'original');

        $names = array_unique($names);
        $names = array_values($names);


        for($t=0;$t<count($names);$t++){
            $codes[str_random(4)] = $names[$t];
        }



        DB::table('column_names')->update(['column_name'=>serialize($codes)]);


    }
}
