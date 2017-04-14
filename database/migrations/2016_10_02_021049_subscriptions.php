<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Subscriptions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('userId')->unsigned();
            $table->string('subscriptionRef');
            $table->string('paidFromMerchant');
            $table->decimal('amountPaid', 8, 4);
            $table->datetime('validUntil');
            $table->enum('subscriptionType', ['Free', '1month', '3months', '6months', '12months']);
            $table->timestamps();
        });

        Schema::table('alerts', function($table) {
            $table->foreign('subscriptionId')->references('id')->on('subscriptions');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('alerts', function($table) {
            $table->dropForeign('alerts_subscriptionid_foreign');
        });

        Schema::dropIfExists('subscriptions');
    }
}
