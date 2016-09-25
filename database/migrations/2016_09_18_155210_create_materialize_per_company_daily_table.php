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
            $table->decimal('openPrice', 16, 4);
            $table->decimal('highPrice', 16, 4);
            $table->decimal('lowPrice', 16, 4);
            $table->decimal('closePrice', 16, 4);
            $table->datetime('tsOpen');
            $table->datetime('tsHigh');
            $table->datetime('tsLow');
            $table->datetime('tsClose');
            $table->decimal('percentChange', 8, 4)->nullable();
            $table->integer('volume')->nullable();
            $table->date('asOf');
            $table->datetime('created_at');
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));

            $table->unique(['companyId', 'asOf']);
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
