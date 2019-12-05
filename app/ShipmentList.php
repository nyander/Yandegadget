<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ShipmentList extends Model
{
    protected $fillable = ['shipment_id','product_id'];
/*
    public function product(){
        return $this->hasMany('App\Products');
    }
*/
}
