<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Aggregate_per_minute extends Model
{
    protected $fillable = ['companyId', 'price', 'asOf', 'percentChange', 'volume'];
}
