<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Condition;

class Condition extends Model
{
    // protected $fillable = ['details','explanation'];

    // public function shipmentlist(){
    //     return $this->hasMany('App\ShipmentList');
    // }

    public function products(){
        return $this->hasMany('App\Product');
    }
   
}
