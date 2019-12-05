<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Condition extends Model
{
    protected $fillable = ['details'];
/*
    public function shipmentlist(){
        return $this->hasMany('App\ShipmentList');
    }

    public function product(){
        return $this->hasMany('App\Product');
    }
   */ 
}
