<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAggregatePerMinuteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('aggregate_per_minute', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('companyId');
            $table->decimal('price', 16, 4);
            $table->datetime('asOf');
            $table->decimal('percentChange', 8, 4);
            $table->integer('volume');
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
        Schema::dropIfExists('aggregate_per_minute');
    }
}
