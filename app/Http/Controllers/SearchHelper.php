<?php
/**
 * Created by PhpStorm.
 * User: Sahand
 * Date: 1/9/19
 * Time: 12:52 PM
 */

namespace App\Http\Controllers;


use App\Product;

class SearchHelper
{

    /*
     * returns categories and subcategories of a particular
     */
    public function getSubcategories($product)
    {

        try{

            $product = Product::where('product_name', $product)->firstOrFail();
        }catch (\Exception $exception){

            return 'product name not found';
        }
        $menu = [];

            try {
                $subCategories = $product->subcategories;

                foreach ($subCategories as $item => $subCategory) {
                    $menu['product'] = str_replace('_',' ',$product->product_name);
                    $arr =  $subCategory->underlays->pluck('name')->toArray();
                    for($i=0 ;$i<count($arr); $i++){
                        $arr[$i] = substr($arr[$i],0,strlen($arr[$i])-4);
                        $arr[$i] = str_replace('_',' ',$arr[$i]);
                    }
                    $menu['category'][str_replace('_',' ',$product->subcategories->pluck('name')->toArray()[$item])] = $arr;
                    unset( $menu['category'][$item]);
                }
            } catch (\Exception $exception) {

            }

        return $menu;

    }

    public function getAllCategories(){
        $menu = [];
        $products = Product::all();
        foreach ($products as $key => $product){
            $menu[$key]['product'] = str_replace('_',' ',$product->product_name);
            $menu[$key]['category'] = str_replace('_',' ',$product->subcategories->pluck('name')->toArray());
            $subCategories = $product->subcategories;
            foreach ($subCategories as $item => $subCategory) {
                $arr =  $subCategory->underlays->pluck('name')->toArray();
                for($i=0 ;$i<count($arr); $i++){
                    $arr[$i] = substr($arr[$i],0,strlen($arr[$i])-4);
                    $arr[$i] = str_replace('_',' ',$arr[$i]);
                }
                $menu[$key]['category'][str_replace('_',' ',$product->subcategories->pluck('name')->toArray()[$item])] = $arr;
                unset( $menu[$key]['category'][$item]);
            }
        }
        return ($menu);
    }

}