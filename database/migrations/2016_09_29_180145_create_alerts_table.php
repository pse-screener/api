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
            // $table->integer('userId');
            $table->string('subscriptionId');
            $table->integer('companyId');
            $table->enum('alertCondition', ['movesAbove', 'movesBelow']);
            $table->decimal('price', 8, 2);
            $table->boolean('sentToSms')->default(0);
            $table->boolean('sentToEmail')->default(0);
            $table->boolean('sendSms')->default(0);
            $table->boolean('sendEmail')->default(0);
            $table->timestamps();

            $table->unique(['companyId', 'alertCondition']);
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
