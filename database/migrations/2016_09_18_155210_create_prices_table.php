<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePricesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('prices', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('companyId');
            $table->decimal('open', 15, 4);
            $table->decimal('high', 15, 4);
            $table->decimal('low', 15, 4);
            $table->decimal('close', 15, 4);
            $table->datetime('tsOpen');
            $table->datetime('tsHigh');
            $table->datetime('tsLow');
            $table->datetime('tsClose');
            $table->decimal('closePercentChange');
            $table->decimal('closeVolume');
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
        Schema::drop('prices');
    }
}
