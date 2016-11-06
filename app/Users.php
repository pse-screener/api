<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Users extends Model
{
	protected $fillable = [
        'fName', 'lName', 'gender', 'birthday', 'email', 'mobileNo'
    ];

    /*public function alerts()
    {
    	return $this->hasMany('App\Alerts',);
    }*/
}
