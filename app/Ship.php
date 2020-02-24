<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Ship extends Model
{
   protected $fillable = ['user_id', 'shipped', 'shipment_company', 'shipment_date' , 'shipment_cost', 'shipment_notes'];


    public function user()
    {
        return $this->belongsTo('App/User');
    }

    public function products()
    {
        return $this->belongsToMany('App\Product');
    }
}
