<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class products extends Model
{
   protected $fillable = ['name','type','cost','supplier','purchase_Date','condition','condition_Notes','selling_Price','recieved','shipment','sold','sold_To'];
}
