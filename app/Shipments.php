<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Shipments extends Model
{
    protected $fillable = ['company','cost','dateOfShipment'];
}
