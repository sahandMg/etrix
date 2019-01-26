<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Component extends Model
{
    use RelationshipsTrait;
    protected $fillable=['persian_name','name','subcategory_id','product_id'];

    public function persianName(){

        return $this->hasOne(PersianName::class);
    }
    public function product(){
        return $this->belongsTo(Product::class);
    }
    public function commons(){
        return $this->hasMany('App\Common');
    }
    public function subcategory(){
        return $this->belongsTo(SubCategory::class,'sub_category_id');
    }

    public function underlay(){
        return $this->belongsTo(Underlay::class,'underlay_id');
    }


}
