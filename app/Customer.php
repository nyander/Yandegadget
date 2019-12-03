<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = ['firstName','lastName','email','phone','postcode','address'];

    public function product(){
        return $this->hasMany('App\Product');
    }
}


