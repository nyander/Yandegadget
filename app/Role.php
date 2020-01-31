<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    //THis function means that a Role can consist of many users       
    public function users(){
        return $this->belongsToMany('App\User'); 
    }
}
