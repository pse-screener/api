<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDailyAggregationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('daily_aggregations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('companyId');
            $table->decimal('price', 16, 4);
            $table->datetime('asOf');
            $table->decimal('percentChange', 8, 4)->nullable();
            $table->integer('volume')->nullable();
            $table->boolean('lastNewPrice');
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
        Schema::dropIfExists('daily_aggregations');
    }
}
