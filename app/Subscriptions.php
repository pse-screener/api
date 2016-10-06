<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Subscriptions extends Model
{
    public function alerts()
    {
    	// return $this->hasMany('App\Alert', 'subscriptionId');
    }
}
