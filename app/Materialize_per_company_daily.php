<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Materialize_per_company_daily extends Model
{
    protected $fillable = [
    	'companyId','open','high','low','close','tsOpen',
    	'tsHigh','tsLow','tsClose','percentChange','volume', 'asOf',
    ];
}
