<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class suppliers extends Model
{
    protected $fillable = ['name','address','city','contact','email'];
}
