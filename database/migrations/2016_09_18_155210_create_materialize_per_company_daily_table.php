<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMaterializePerCompanyDailyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('materialize_per_company_daily', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('companyId');
            $table->decimal('open', 16, 4);
            $table->decimal('high', 16, 4);
            $table->decimal('low', 16, 4);
            $table->decimal('close', 16, 4);
            $table->datetime('tsOpen');
            $table->datetime('tsHigh');
            $table->datetime('tsLow');
            $table->datetime('tsClose');
            $table->decimal('percentChange', 8, 4);
            $table->integer('volume');
            $table->date('asOf');
            $table->timestamps();

            $table->index(['companyId', 'asOf']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('materialize_per_company_daily');
    }
}
