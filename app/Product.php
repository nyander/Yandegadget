<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Product;

class Product extends Model
{
protected $fillable = ['name','type','cost','supplier','purchase_Date','condition','condition_Notes','selling_Price','received','sold'];
    
    public function supplier(){
        return $this->belongsTo('App\Supplier');
    }
    
    public function category(){
        return $this->belongsTo('App\Category');
    }

    public function user(){
        return $this->belongsTo('App\User');
    }    

    public function condition(){
        return $this->belongsTo('App\Condition');
    }

    
    public function images()
    {
        return $this->hasMany('App\Image');
    }
    
}

