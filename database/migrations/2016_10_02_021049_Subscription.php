<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Subscription extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Subscription', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('userId');
            $table->string('subscriptionRef');
            $table->string('PaidFromMerchant');
            $table->decimal('amountPaid', 8, 2);
            $table->datetime('validUntil');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Subscription');
    }
}
