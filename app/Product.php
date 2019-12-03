<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Product;

class Product extends Model
{
protected $fillable = ['name','type','cost','supplier','purchase_Date','condition','condition_Notes','selling_Price','recieved','sold','sold_To'];
    
    public function supplier(){
        return $this->belongsTo('App\Suppliers');
    }

    public function customer(){
        return $this->belongsTo('App\Customers');
    }

    public function category(){
        return $this->belongsTo('App\Categories');
    }

    public function condition(){
        return $this->belongsTo('App\Conditions');
    }

    public function request(){
        return $this->belongsTo();
    }
}

