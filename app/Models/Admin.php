<?php

namespace App\Models;


use Illuminate\Foundation\Auth\User as Authenticatable;
class Admin extends Authenticatable
{
    
    protected $guard = 'admin';
    protected $fillable = [
        'name', 'email', 'password','mobile','address','type','status',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

}
