<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Alerts extends Model
{
    protected $fillable = ['companyId', 'priceCondition'];
}
