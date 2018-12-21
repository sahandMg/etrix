<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;

class SubCategory extends Model
{
    protected $fillable = ['name'];

    public function underlays(){

        return $this->hasMany(Underlay::class);
    }

    public function components(){

        return $this->hasMany(Component::class);
    }
}
