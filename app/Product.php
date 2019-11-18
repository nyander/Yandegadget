<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
   protected $fillable = ['product_Name','product_Type','product_Cost','product_Supplier','purchase_Date','condition','condition_Notes','selling_Price','recieved','shipment','sold','sold_To'];
}
