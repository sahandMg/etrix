<?php

namespace App\Http\Controllers;

use App\Helper;
use App\Product;

use Illuminate\Http\Request;

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
    $presetColumns = ['id','ld_image','hd_image','datasheet','footprint','manufacturer_part_number',
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

                    }
                    if(!isset($underlay)){
                        $data = [
                            'category' => [$product->product_name, $subcats],
                            'component_id' => $product->components[$t]->id,
                            'product_id' => $product->id,
                            'columns' => array_merge($presetColumns,$names)
                        ];
                    }else{
                        $data = [
                            'num'=>$t,
                            'category' => [$product->product_name, $subcats,$underlay],
                            'component_id' => $product->components[$t]->id,
                            'product_id' => $product->id,
                            'columns' => array_merge($presetColumns,$names)
                        ];
                    }

                array_push($set,$data);
                }

//            }catch (\Exception $exception){

//                return [$exception,$i];
//            }
        }

        return $set;

    }
}
