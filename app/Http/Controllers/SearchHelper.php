<?php
/**
 * Created by PhpStorm.
 * User: Sahand
 * Date: 1/9/19
 * Time: 12:52 PM
 */

namespace App\Http\Controllers;


use App\Product;
use App\Repository\ColumnCode;
use Illuminate\Support\Facades\DB;

class SearchHelper
{

    public $skip = 20;
    public $type;
    public $filteredColumn;
    /*
     * returns categories and subcategories of a particular
     */
    public function getSubcategories($product)
    {

        try {

            $product = Product::where('product_name', $product)->firstOrFail();
        } catch (\Exception $exception) {

            return 'product name not found';
        }
        $menu = [];

        try {
            $subCategories = $product->subcategories;

            foreach ($subCategories as $item => $subCategory) {
                $menu['product'] = str_replace('_', ' ', $product->product_name);
                $arr = $subCategory->underlays->pluck('name')->toArray();
                for ($i = 0; $i < count($arr); $i++) {
                    $arr[$i] = substr($arr[$i], 0, strlen($arr[$i]) - 4);
                    $arr[$i] = str_replace('_', ' ', $arr[$i]);
                }
                $menu['category'][str_replace('_', ' ', $product->subcategories->pluck('name')->toArray()[$item])] = $arr;
                unset($menu['category'][$item]);
            }
        } catch (\Exception $exception) {

        }

        return $menu;

    }

    public function getAllCategories()
    {
        $menu = [];
        $products = Product::all();
        foreach ($products as $key => $product) {
            $menu[$key]['product'] = str_replace('_', ' ', $product->product_name);
            $menu[$key]['category'] = str_replace('_', ' ', $product->subcategories->pluck('name')->toArray());
            $subCategories = $product->subcategories;
            foreach ($subCategories as $item => $subCategory) {
                $arr = $subCategory->underlays->pluck('name')->toArray();
                for ($i = 0; $i < count($arr); $i++) {
                    $arr[$i] = substr($arr[$i], 0, strlen($arr[$i]) - 4);
                    $arr[$i] = str_replace('_', ' ', $arr[$i]);
                }
                $menu[$key]['category'][str_replace('_', ' ', $product->subcategories->pluck('name')->toArray()[$item])] = $arr;
                unset($menu[$key]['category'][$item]);
            }
        }
        return ($menu);
    }

    /*
     * find the keyword in database
     */
    public function findKeyword($keyword, $paginate)
    {
        $commonGroup = DB::table('commons')
            ->where('manufacturer_part_number', 'like', "%$keyword%")
            ->skip(($this->skip * ($paginate - 1)))->take($this->skip)
            ->get();
//     save unknown keyword in failed_parts table
        $this->type = 30;
        if ($commonGroup->isEmpty()) {
            $query = DB::table('failed_parts')->where('manufacturer_part_number', $keyword)->first();
            if ($query != null) {
                DB::table('failed_parts')->where('manufacturer_part_number', $keyword)->update(['repeat' => $query->repeat + 1]);
            } else {
                DB::table('failed_parts')->insert(['manufacturer_part_number' => $keyword, 'repeat' => 1]);
            }

            return '415';
        }
        // check if requested part is available in multiple categories
         $uniqueComponentIds = array_values(array_unique($commonGroup->pluck('component_id')->toArray()));
        if(count($uniqueComponentIds) > 1){
            // find categories with their products
            $breadCrumbs = [];
            for($m=0;$m<count($uniqueComponentIds);$m++){

                array_push($breadCrumbs,$this->makeBreadCrumb(get_object_vars($commonGroup->where('component_id',$uniqueComponentIds[$m])->first())));
            }
            return $breadCrumbs;
        }

        // finding the separate parts of this group
        $separateGroup = [];
        for ($i = 0; $i < count($commonGroup); $i++) {

            try {

                $modelName = DB::table('components')->where('id', $commonGroup[$i]->component_id)->first()->name;
            } catch (\Exception $exception) {

                return 'model name not found in search helper at 102';
            }

            $model = 'App\IC\\' . $modelName;
            $instance = new $model();
            $modelTableName = $instance->getTable();
            // creates array of separates
            $partSeparate = DB::table($modelTableName)->where('common_id', $commonGroup[$i]->id)->first();
            // check if there is separate part available the connects to common part
            if (is_null($partSeparate)) {

                return 'no separate found for id number' . $commonGroup[$i]->id . ' in commos';
            }

            array_push($separateGroup, $partSeparate);
        }
        /// separate part fetching finished

        // now join this two parts to get a complete data
        $completePart = [];
        for ($t = 0; $t < count($separateGroup); $t++) {

            array_push($completePart, array_merge(get_object_vars($commonGroup[$t]), get_object_vars($separateGroup[$t])));
        }
        // complete array of part data has been created !

        // put  filter's code here
        $columnContent = $this->makeFilters($completePart);
        // -------------------------------------
        $breadCrumb = $this->makeBreadCrumb($completePart[0]);
        $modifiedPartArray = $this->removeExtraData($completePart);
        $codes = $this->getColumnCodes($columnContent);
        // Filter
        if(isset($_GET['filters']) && !isset($_GET['order'])){
            $breadCrumb = $this->makeBreadCrumb($completePart[0]);
            $modifiedPartArray = $this->removeExtraData($completePart);
            $result = $this->doFiltering($keyword,$subcategory = null,$modifiedPartArray);
            if($result == '415'){
                return 'Filter Values Not Found In Search Result';
            }
            $modifiedFilteredPartArray = $result[0];
            $columnContent = $result[1];
            $codes = $this->getColumnCodes($columnContent);
            array_push($modifiedFilteredPartArray,$columnContent,$codes,$breadCrumb,$this->filteredColumn);
            $modifiedFilteredPartArray = $this->makeMamadFormat($modifiedFilteredPartArray);
            return $modifiedFilteredPartArray;
        }

        // Sort
        if(!isset($_GET['filters']) && isset($_GET['order'])){
            if(!isset($_GET['order'])){
                return 'Sort Order Is Not Defined';
            }else{
                $order = $_GET['order'];
            }
            if(!isset($_GET['colName'])){
                return 'Column Name Not Defined!';
            }else{
                $colName = $_GET['colName'];
            }
            if(!isset($_GET['subcategory'])){
//                return 'Send A Sub Category!';
            }
            $sortedArrayPart = $this->sortTable($completePart,$colName,$order);
            $modifiedSortedPartArray = $this->removeExtraData($sortedArrayPart);
            array_push($modifiedSortedPartArray,$columnContent,$codes,$breadCrumb);
            $modifiedSortedPartArray = $this->makeMamadFormat($modifiedSortedPartArray);
            return $modifiedSortedPartArray ;
        }
        // Sort + Filter
        if(isset($_GET['filters']) && isset($_GET['order'])){
            $breadCrumb = $this->makeBreadCrumb($completePart[0]);
            $result = $this->doFiltering($keyword,$subcategory = null,$completePart);
            if($result == '415'){
                return 'Filter Values Not Found In Search Result';
            }
            $modifiedFilteredPartArray = $result[0];
            if(!isset($_GET['order'])){
                return 'Sort Order Is Not Defined';
            }else{
                $order = $_GET['order'];
            }
            if(!isset($_GET['colName'])){
                return 'Column Name Not Defined!';
            }else{
                $colName = $_GET['colName'];
            }
            if(!isset($_GET['subcategory'])){
//                return 'Send A Sub Category!';
            }

            $sortedArrayPart = $this->sortTable($modifiedFilteredPartArray,$colName,$order);
            $modifiedFilteredSortedPartArray = $this->removeExtraData($sortedArrayPart);
            $columnContent = $result[1];
            $codes = $this->getColumnCodes($columnContent);
            array_push($modifiedFilteredSortedPartArray,$columnContent,$codes,$breadCrumb,$this->filteredColumn);
            $modifiedFilteredSortedPartArray =  $this->makeMamadFormat($modifiedFilteredSortedPartArray);
            return $modifiedFilteredSortedPartArray;
        }


        array_push($modifiedPartArray,$columnContent,$codes,$breadCrumb);
        $modifiedPartArray = $this->makeMamadFormat($modifiedPartArray);
        return $modifiedPartArray;

    }

    /*
     * get all parts from a specific subcategory
     * subcategory == underlay in database
     */
    public function getPartsFromSubcategory($product,$subcategory, $paginate)
    {
        $this->type = 40;
        try{

            $product_id = DB::table('products')->where('product_name',$product)->first()->id;
        }catch (\Exception $exception){
            return 'Product not found in database';
        }
        try {
            $componentId = DB::table('components')->where('product_id',$product_id)->where('name', 'like', "%$subcategory%")->first()->id;
        } catch (\Exception $exception) {
            return 'component not found on search helper at getPartsFromSubcategory';
        }
        $commonPart = DB::table('commons')
            ->where('component_id', $componentId)
            ->skip(($this->skip * ($paginate - 1)))->take($this->skip)
            ->get();

        if (!$commonPart->isEmpty()) {

            $completePart = $this->MakeCompletePartArray($commonPart->toArray());
            if(is_string($completePart)){
                return $completePart;
            }
            $modifiedPartArray = $this->removeExtraData($completePart);
            $breadCrumb = $this->makeBreadCrumb($completePart[0]);
            $columnContent = $this->makeFilters($modifiedPartArray);
            $codes = $this->getColumnCodes($columnContent);

            // Filter
            if(isset($_GET['filters']) && !isset($_GET['order'])){
                $breadCrumb = $this->makeBreadCrumb($completePart[0]);
                $modifiedPartArray = $this->removeExtraData($completePart);
                $result = $this->doFiltering($keyword = null,$subcategory ,$modifiedPartArray);
                if($result == '415'){
                    return 'Filter Values Not Found In Search Result';
                }

                $modifiedFilteredPartArray = $result[0];
                $columnContent = $result[1];

                $codes = $this->getColumnCodes($columnContent);
                array_push($modifiedFilteredPartArray,$columnContent,$codes,$breadCrumb,$this->filteredColumn);
                $modifiedFilteredPartArray = $this->makeMamadFormat($modifiedFilteredPartArray);
                return $modifiedFilteredPartArray;
            }
            // Sort
            if(!isset($_GET['filters']) && isset($_GET['order'])){
                if(!isset($_GET['order'])){
                    return 'Sort Order Is Not Defined';
                }else{
                    $order = $_GET['order'];
                }
                if(!isset($_GET['colName'])){
                    return 'Column Name Not Defined!';
                }else{
                    $colName = $_GET['colName'];
                }
                if(!isset($_GET['subcategory'])){
//                return 'Send A Sub Category!';
                }
                $sortedArrayPart = $this->sortTable($completePart,$colName,$order);
                $modifiedSortedPartArray = $this->removeExtraData($sortedArrayPart);
                array_push($modifiedSortedPartArray,$columnContent,$codes,$breadCrumb);
                $modifiedSortedPartArray = $this->makeMamadFormat($modifiedSortedPartArray);
                return $modifiedSortedPartArray ;
            }
            // Sort + Filter
            if(isset($_GET['filters']) && isset($_GET['order'])){
                $breadCrumb = $this->makeBreadCrumb($completePart[0]);
                $result = $this->doFiltering($keyword = null,$subcategory ,$completePart);
                if($result == '415'){
                    return 'Filter Values Not Found In Search Result';
                }
                $modifiedFilteredPartArray = $result[0];
                if(!isset($_GET['order'])){
                    return 'Sort Order Is Not Defined';
                }else{
                    $order = $_GET['order'];
                }
                if(!isset($_GET['colName'])){
                    return 'Column Name Not Defined!';
                }else{
                    $colName = $_GET['colName'];
                }
                if(!isset($_GET['subcategory'])){
//                return 'Send A Sub Category!';
                }
                $sortedArrayPart = $this->sortTable($modifiedFilteredPartArray,$colName,$order);
                $modifiedFilteredSortedPartArray = $this->removeExtraData($sortedArrayPart);
                $columnContent = $result[1];
                $codes = $this->getColumnCodes($columnContent);
                array_push($modifiedFilteredSortedPartArray,$columnContent,$codes,$breadCrumb,$this->filteredColumn);
                $modifiedFilteredSortedPartArray = $this->makeMamadFormat($modifiedFilteredSortedPartArray);
                return $modifiedFilteredSortedPartArray;
            }
            array_push($modifiedPartArray,$columnContent,$codes,$breadCrumb);
            $modifiedPartArray = $this->makeMamadFormat($modifiedPartArray);
            return $modifiedPartArray;
        } else {

            return 'common not found on search helper at getPartsFromSubcategory ';
        }

    }

    /*
     * this helper gets array of commons data and returns complete array of part data
     */

    public function MakeCompletePartArray($commonGroup)
    {

        $separateGroup = [];
        for ($i = 0; $i < count($commonGroup); $i++) {

            try {

                $modelName = DB::table('components')->where('id', $commonGroup[$i]->component_id)->first()->name;
            } catch (\Exception $exception) {

                return 'model name not found in search helper at 168';
            }

            $model = 'App\IC\\' . $modelName;
            $instance = new $model();
            $modelTableName = $instance->getTable();
            // creates array of separates
            $partSeparate = DB::table($modelTableName)->where('common_id', $commonGroup[$i]->id)->first();

            // check if there is separate part available the connects to common part
            if (is_null($partSeparate)) {

                return 'no separate found for id number ' . $commonGroup[$i]->id . ' in commons';
            }

            array_push($separateGroup, $partSeparate);
        }
        /// separate part fetching finished
        // now join this two parts to get a complete data
        $completePart = [];
        for ($t = 0; $t < count($separateGroup); $t++) {

            array_push($completePart, array_merge(get_object_vars($commonGroup[$t]), get_object_vars($separateGroup[$t])));
        }
        // complete array of part data has been created !

        return $completePart;


    }


    /*
     * create filters by considering available parts
     */
    public function makeFilters(array $partArray){
    // remove some extra column
        $partArray = $this->removeExtraData($partArray);
        $columnContent = [];
        $filteredCols = [];
        $j = 0;
        for($t = 0;$t<count($partArray);$t++) {
            $columnNames = array_keys($partArray[$t]);

        for($i = 0;$i<count($columnNames);$i++){

            $columnContent[$columnNames[$i]][$j] = $partArray[$t][$columnNames[$i]];

            $columnContent[$columnNames[$i]] =  array_unique($columnContent[$columnNames[$i]]);
            $columnContent[$columnNames[$i]] =  array_values($columnContent[$columnNames[$i]]);

        }
            $j++;
        }
        if(isset($columnNames)){
            for($i = 0;$i<count($columnNames);$i++) {
                // checks if there is just , one data in filter array, then removes it from filters
                if (count($columnContent[$columnNames[$i]]) == 1) {
                    unset($columnContent[$columnNames[$i]]);
                }
            }

        }else{
            return 'column names not defined';
        }

            return $columnContent;
    }

    public function doFiltering($keyword = null,$subcategory = null,$modifiedPartArray){


        if(is_null($keyword)){
            // filter on subcategory parts
            if(is_null($subcategory)){
                return 'send a subcategory';
            }
            $filters=[];
            $str = $_SERVER['QUERY_STRING'];
            parse_str($str, $queries);
            $keys = array_keys($queries);

            $filters_name = array_splice($keys,array_search('filters',$keys)+1);
            for($i=0;$i<count($filters_name);$i++){

                $filters[$filters_name[$i]] = $queries[$filters_name[$i]];
            }
            $code = new ColumnCode();
            $filtersNameArray = $code->getFilter($filters);
            $result = $this->filterResultsWithFilterNames($filtersNameArray,$modifiedPartArray);
            if($result == '415'){
                return '415';
            }else{
                return $result;
            }
        }



        elseif(is_null($subcategory)){

            if(is_null($keyword)){
                return 'send a keyword';
            }

            $filters=[];
            $str = $_SERVER['QUERY_STRING'];
            parse_str($str, $queries);
            $keys = array_keys($queries);

            $filters_name = array_splice($keys,array_search('filters',$keys)+1);
            for($i=0;$i<count($filters_name);$i++){

                $filters[$filters_name[$i]] = $queries[$filters_name[$i]];
            }
            $code = new ColumnCode();
            $filtersNameArray = $code->getFilter($filters);

           $result = $this->filterResultsWithFilterNames($filtersNameArray,$modifiedPartArray);
            if($result == '415'){
                return 'no column found to filter through !';
            }else{
                return $result;
            }

        }else{
            return 'Wrong Function Has Executed!';
        }

    }

    public function filterResultsWithFilterNames($filtersNameArray,$modifiedPartArray){

        $this->filteredColumn = $this->getColumnCodes($filtersNameArray);

        $filteredData = [];
        $sign = 0;
        for($i=0;$i<count($modifiedPartArray);$i++){

            foreach ($modifiedPartArray[$i] as $key => $item){

                if(in_array($key,array_keys($filtersNameArray))){
                   $keyPosInFiltersNameArray = array_search($key,array_keys($filtersNameArray));
                    $filterValues = $filtersNameArray[array_keys($filtersNameArray)[$keyPosInFiltersNameArray]]; // 0 => 72MHz , 1 => 100MHz
                    // search this values in $modifiedPartArray to filter data

                            // speed value
                        if(in_array($modifiedPartArray[$i][array_keys($filtersNameArray)[$keyPosInFiltersNameArray]],$filterValues)){
                            array_push($filteredData,$modifiedPartArray[$i]);
                            $sign++;
                        }

                }

            }
        }
        if($sign == 0){

            return '415';
        }
        // rebuild filters
        $filters = $this->makeFilters($filteredData);
        return [$filteredData,$filters];
    }

    public function sortTable(array $completePartArray,$sortColName,$sortOrder){

        for($i=0;$i<count($completePartArray);$i++){

            $sortColData[$completePartArray[$i]['id']] = $completePartArray[$i][$sortColName];
        }



        for($i=0;$i<count($completePartArray);$i++) {


        $cols[$completePartArray[$i]['id']] = $completePartArray[$i][$sortColName];
        $cols[$completePartArray[$i]['id']] = str_replace('~','',$cols[$completePartArray[$i]['id']]);
        $str[$completePartArray[$i]['id']] = explode(" ",$cols[$completePartArray[$i]['id']])[0];
        $volts[$completePartArray[$i]['id']]  = preg_replace('/[^-0-9\.]/', '',$str[$completePartArray[$i]['id']]);
        /*
         * µ , n , m , k , K , M , G
        */

        if(strpbrk($str[$completePartArray[$i]['id']],'n')){
            $volts[$completePartArray[$i]['id']] = $volts[$completePartArray[$i]['id']] /pow(10,9);
        }elseif(strpbrk($str[$completePartArray[$i]['id']],'µ')){
            $volts[$completePartArray[$i]['id']] = $volts[$completePartArray[$i]['id']] /pow(10,6);
        }elseif(strpbrk($str[$completePartArray[$i]['id']],'m')){
            $volts[$completePartArray[$i]['id']] = $volts[$completePartArray[$i]['id']] /pow(10,3);
        }elseif(strpbrk($str[$completePartArray[$i]['id']],'k')){
            $volts[$completePartArray[$i]['id']] = $volts[$completePartArray[$i]['id']] * pow(10,3);
        }elseif(strpbrk($str[$completePartArray[$i]['id']],'K')){
            $volts[$completePartArray[$i]['id']] = $volts[$completePartArray[$i]['id']] * pow(10,3);
        }
        elseif(strpbrk($str[$completePartArray[$i]['id']],'M')){
            $volts[$completePartArray[$i]['id']] = $volts[$completePartArray[$i]['id']] * pow(10,6);
        }elseif(strpbrk($str[$completePartArray[$i]['id']],'G')) {
            $volts[$completePartArray[$i]['id']] = $volts[$completePartArray[$i]['id']] * pow(10,9);
//        }

        }

    }
//        $volts = not common table Ids
            if($sortOrder == 'desc'){

                arsort($volts);
            }else{
                asort($volts);
            }
            $SortedResult = $volts;
            $sortedPartArray = [];
        for($j = 0; $j <count($completePartArray); $j++) {

            for($t = 0; $t <count($completePartArray); $t++){

                if ($completePartArray[$t]['id'] == array_keys($SortedResult)[$j]) {
                        array_push($sortedPartArray, $completePartArray[$t]);
                    }
                }
            }

            return $sortedPartArray;

    }

    public function makeBreadCrumb(array $completePart){

        try{

            $breadCrumb = DB::table('components')->where('components.id',$completePart['component_id'])
                ->join('products','components.product_id','=','products.id')->select('name','product_name')->get();
        }catch (\Exception $exception){
            return 'breadcrumb problem <br>'.$exception;
        }
        return get_object_vars($breadCrumb[0]);

    }

    public function getColumnCodes(array $columnContent){

        $code = new ColumnCode();
        return $code->sendFilter($columnContent);
    }


    public function makeMamadFormat(array $completePartArray){
        $finalArray = [];
        $count = count($completePartArray);
        $type = $this->type;

        if(!is_null($this->filteredColumn)){
            $filtersArray = $completePartArray[$count-4];
            unset($completePartArray[$count - 4]);
            $filtersCodeArray = $completePartArray[$count - 3];
            unset($completePartArray[$count - 3]);
            $breadCrumb = $completePartArray[$count - 2];
            unset($completePartArray[$count - 2]);
            $filteredCols = $completePartArray[$count - 1];
            unset($completePartArray[$count - 1]);

            array_push($finalArray,$type);
            array_push($finalArray,null);
            array_push($finalArray,$completePartArray);
            array_push($finalArray,$filtersArray);
            array_push($finalArray,false);
            array_push($finalArray,$filtersCodeArray);
            array_push($finalArray,$breadCrumb);
            array_push($finalArray,$filteredCols);
            return $finalArray;

        }

        $filtersArray = $completePartArray[$count-3];
        unset($completePartArray[$count - 3]);
        $filtersCodeArray = $completePartArray[$count - 2];
        unset($completePartArray[$count - 2]);
        $breadCrumb = $completePartArray[$count - 1];
        unset($completePartArray[$count - 1]);
        array_push($finalArray,$type);
        array_push($finalArray,null);
        array_push($finalArray,$completePartArray);
        array_push($finalArray,$filtersArray);
        array_push($finalArray,false);
        array_push($finalArray,$filtersCodeArray);
        array_push($finalArray,$breadCrumb);
        return $finalArray;
    }

    public function removeExtraData(array $partArray){

        for($i=0;$i<count($partArray);$i++){
            unset($partArray[$i]['id']);
            unset($partArray[$i]['hd_image']);
            unset($partArray[$i]['footprint']);
            unset($partArray[$i]['component_id']);
            unset($partArray[$i]['part_id']);
            unset($partArray[$i]['model']);
            unset($partArray[$i]['updated_at']);
            unset($partArray[$i]['created_at']);
            unset($partArray[$i]['common_id']);
            unset($partArray[$i]['product_id']);
        }

        return $partArray;

    }
}
