<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Materialize_per_company_daily extends Model
{
	protected $table = "materialize_per_company_daily";

    protected $fillable = [
    	'companyId','openPrice','highPrice','lowPrice','closePrice','tsOpen',
    	'tsHigh','tsLow','tsClose','percentChange','volume', 'asOf',
    ];
}
