<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Product;

class Product extends Model
{
protected $fillable = ['name','type','cost','supplier','purchase_Date','condition','condition_Notes','selling_Price','recieved','sold','sold_To'];
    /*
    public function supplier(){
        return $this->belongsTo('App\Supplier');
    }

        public function category(){
        return $this->belongsTo('App\Category');
    }

    public function condition(){
        return $this->belongsTo('App\Condition');
    }

    public function request(){
        return $this->belongsTo('App\ProductRequest');
    }
    
    public function customer(){
        return $this->belongsTo('App\Customer');
    }

    public function shipmentlist(){
        return $this->belongsTo('App\ShipmentList');
    }
    */
}

