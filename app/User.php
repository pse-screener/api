<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
<<<<<<< HEAD
        'name', 'email', 'password', 'test'
=======
        'name', 'email', 'password', 'mobileNo'
>>>>>>> ad8fe912dfa7ee52cbf85cce34c9c5ada633f053
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
<<<<<<< HEAD
        'password', 'remember_token', 'test'
=======
        'password', 'remember_token', 'mobileNo'
>>>>>>> ad8fe912dfa7ee52cbf85cce34c9c5ada633f053
    ];
}
