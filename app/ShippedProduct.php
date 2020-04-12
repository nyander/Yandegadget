<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ShippedProduct extends Model
{
    protected $table = 'shipped_product';

    protected $fillable = ['shipment_id', 'product_id', 'received'];
}
