<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSystemSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->increments('id');
            $table->date('last_eod');
            $table->boolean('disable_eod');
            $table->datetime('last_exec_eod');
        });

        DB::table('system_settings')->insert(array('last_eod' => '2016-09-23 16::00',
            'disable_eod' => 1, 'last_exec_eod' => '2016-09-23 16::00')
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('system_settings');
    }
}
