<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Underlay extends Model
{
    protected $fillable = ['name','subcategory_id'];

//    public function subcategories(){
//
//        return $this->belongsToMany(SubCategory::class);
//    }
}
