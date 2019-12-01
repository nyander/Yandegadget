<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Products extends Model
{
protected $fillable = ['name','type'/*,'cost','supplier','purchase_Date','condition','condition_Notes','selling_Price','recieved','sold','sold_To'*/];
    
    public function supplier(){
        return $this->belongsTo('App\suppliers');
    }
}

