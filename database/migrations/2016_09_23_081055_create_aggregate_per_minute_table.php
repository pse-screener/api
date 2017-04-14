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
            $table->bigInteger('volume');
            $table->boolean('materialized')->nullable();
            $table->datetime('created_at');
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
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
