<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Suppliers extends Model
{
    protected $fillable = ['name','address','city','contact','email'];

    public function products(){
        return $this->hasMany('App/products');
    }
}
