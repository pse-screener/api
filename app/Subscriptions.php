<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Subscriptions extends Model
{
    protected $fillable = ['userId', 'subscriptionRef', 'paidFromMerchant', 'amountPaid', 'validUntil'];
}
