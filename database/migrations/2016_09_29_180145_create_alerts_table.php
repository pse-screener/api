<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAlertsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('alerts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('subscriptionId')->unsigned();
            $table->integer('companyId')->unsigned();
            $table->enum('priceCondition', ['movesAbove', 'movesBelow']);
            $table->decimal('price', 8, 2);
            $table->boolean('sentToSms')->default(0);
            $table->boolean('sentToEmail')->default(0);
            $table->boolean('sendSms')->default(0);
            $table->boolean('sendEmail')->default(0);
            $table->timestamps();

            $table->unique(['companyId', 'priceCondition', 'subscriptionId']);
            $table->foreign('companyId')->references('id')->on('companies');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('alerts');
    }
}
