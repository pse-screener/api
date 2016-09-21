<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Price extends Model
{
    protected $fillable = ['open','high','low','close','tsOpen','tsHigh','tsLow','tsClose','closePercentChange','closeVolume'];
}
