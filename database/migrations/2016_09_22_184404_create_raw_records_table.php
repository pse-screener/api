<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRawRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('raw_records', function (Blueprint $table) {
            $table->increments('id');
            $table->string('symbol');
            $table->string('companyName');
            $table->decimal('amount', 16, 4);
            $table->decimal('percentChange', 8, 4);
            $table->integer('volume');
            $table->datetime('asOf');
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
        Schema::dropIfExists('raw_records');
    }
}
