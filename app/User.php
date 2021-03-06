<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function roles()
    {
        return $this->belongstoMany('App\Role'); 
    }

    //checks if thr user has any role 
    public function hasAnyRoles($roles){
        // this is basically first checking roles relationship 
        // and if their is any roles assigned. then it will chekc the first one
        if($this->roles()->whereIn('name', $roles)->first()){
            return true;
        }
        return false;
    } 

    public function hasRole($role)
    {
        // this is basically first checking roles relationship 
        // and if their is a roles assigned. then it will check the first one
        if($this->roles()->where('name', $role)->first()){
            return true;
        }
        return false;
    } 

    public function shippedProducts()
    {
        return $this->hasMany('App\Ship');
    }
}
