<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Price extends Model
{
    protected $fillable = [
    	'companyId','open','high','low','close','tsOpen',
    	'tsHigh','tsLow','tsClose','percentChange','volume',
    ];
}
