<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Customers extends Model
{
    protected $fillable = ['firstName','lastName','email','phone','postcode','address'];
}
