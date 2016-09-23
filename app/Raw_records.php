<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Raw_records extends Model
{
    protected $fillable = [
    	'symbol', 'amount', 'percentChange', 'volume', 'asOf',
    ];
}
