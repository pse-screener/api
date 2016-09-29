<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSmsAlertsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sms_alerts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('userId');
            $table->string('paypalRef');
            $table->decimal('amountPaid', 8, 2);
            $table->decimal('alertAbovePrice', 8, 2);
            $table->decimal('alertBelowPrice', 8, 2);
            $table->datetime('startDate');
            $table->datetime('endDate');
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
        Schema::dropIfExists('sms_alerts');
    }
}
