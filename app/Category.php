<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['type'];
/*
    public function product(){
        return $this->hasMany('App\Product');
    }

    public function productRequest(){
        return $this->hasMany('App\ProductRequest');
    }
    */
}
