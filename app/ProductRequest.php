<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductRequest extends Model
{
    protected $fillable = ['name','customer_id','type','condition','available'];
/*
    public function customer(){
        return $this->belongsTo('App\Customer');
    }

    */
}
