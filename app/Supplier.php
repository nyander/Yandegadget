<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class Supplier extends Model
{
    protected $fillable = ['name','address','city','contact','email'];

    public function products(){
        return $this->hasMany('App/Product');
    }
}


