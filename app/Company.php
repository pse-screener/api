<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = ['companyName', 'symbol'];

    public function prices() {
    	return $this->hasMany('App\Materialize_per_company_daily', 'companyId', 'id');
    }
}
