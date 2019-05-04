<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;

class Project extends Model
{
    protected $fillable = ['price'];
    public function cart(){

        return $this->hasOne(Cart::class);
    }

    public function user(){

        return $this->belongsTo(User::class);
    }
}
