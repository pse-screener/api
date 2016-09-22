<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Raw_records extends Model
{
    protected $fillable = [
    	'symbol', 'companyName', 'amount', 'percentChange', 'volume', 'asOf',
    ];
}
