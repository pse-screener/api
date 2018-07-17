<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Telcos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('telcos', function (Blueprint $table) {
            $table->increments('id');
            $table->string('mobilePrefix');
            $table->string('network');
            $table->timestamps();
        });

        $today = date('Y-m-d H:i:s');

        $numbers = array(
            array('mobilePrefix' => '0813', 'network' => 'Smart', 'created_at' => $today, 'updated_at' => $today),
            array('mobilePrefix' => '0907', 'network' => 'Smart', 'created_at' => $today, 'updated_at' => $today),
            array('mobilePrefix' => '0908', 'network' => 'Smart', 'created_at' => $today, 'updated_at' => $today),
            array('mobilePrefix' => '0909', 'network' => 'Smart', 'created_at' => $today, 'updated_at' => $today),
            array('mobilePrefix' => '0910', 'network' => 'Smart', 'created_at' => $today, 'updated_at' => $today),
            array('mobilePrefix' => '0911', 'network' => 'Smart', 'created_at' => $today, 'updated_at' => $today),
            array('mobilePrefix' => '0912', 'network' => 'Smart', 'created_at' => $today, 'updated_at' => $today),
            array('mobilePrefix' => '0913', 'network' => 'Smart', 'created_at' => $today, 'updated_at' => $today),
            array('mobilePrefix' => '0914', 'network' => 'Smart', 'created_at' => $today, 'updated_at' => $today),
            array('mobilePrefix' => '0918', 'network' => 'Smart', 'created_at' => $today, 'updated_at' => $today),
            array('mobilePrefix' => '0919', 'network' => 'Smart', 'created_at' => $today, 'updated_at' => $today),
            array('mobilePrefix' => '0920', 'network' => 'Smart', 'created_at' => $today, 'updated_at' => $today),
            array('mobilePrefix' => '0921', 'network' => 'Smart', 'created_at' => $today, 'updated_at' => $today),
            array('mobilePrefix' => '0928', 'network' => 'Smart', 'created_at' => $today, 'updated_at' => $today),
            array('mobilePrefix' => '0929', 'network' => 'Smart', 'created_at' => $today, 'updated_at' => $today),
            array('mobilePrefix' => '0930', 'network' => 'Smart', 'created_at' => $today, 'updated_at' => $today),
            array('mobilePrefix' => '0938', 'network' => 'Smart', 'created_at' => $today, 'updated_at' => $today),
            array('mobilePrefix' => '0939', 'network' => 'Smart', 'created_at' => $today, 'updated_at' => $today),
            array('mobilePrefix' => '0946', 'network' => 'Smart', 'created_at' => $today, 'updated_at' => $today),
            array('mobilePrefix' => '0947', 'network' => 'Smart', 'created_at' => $today, 'updated_at' => $today),
            array('mobilePrefix' => '0948', 'network' => 'Smart', 'created_at' => $today, 'updated_at' => $today),
            array('mobilePrefix' => '0949', 'network' => 'Smart', 'created_at' => $today, 'updated_at' => $today),
            array('mobilePrefix' => '0950', 'network' => 'Smart', 'created_at' => $today, 'updated_at' => $today),
            array('mobilePrefix' => '0989', 'network' => 'Smart', 'created_at' => $today, 'updated_at' => $today),
            array('mobilePrefix' => '0998', 'network' => 'Smart', 'created_at' => $today, 'updated_at' => $today),
            array('mobilePrefix' => '0999', 'network' => 'Smart', 'created_at' => $today, 'updated_at' => $today),
            array('mobilePrefix' => '0817', 'network' => 'Globe', 'created_at' => $today, 'updated_at' => $today),
            array('mobilePrefix' => '0905', 'network' => 'Globe', 'created_at' => $today, 'updated_at' => $today),
            array('mobilePrefix' => '0906', 'network' => 'Globe', 'created_at' => $today, 'updated_at' => $today),
            array('mobilePrefix' => '0915', 'network' => 'Globe', 'created_at' => $today, 'updated_at' => $today),
            array('mobilePrefix' => '0916', 'network' => 'Globe', 'created_at' => $today, 'updated_at' => $today),
            array('mobilePrefix' => '0917', 'network' => 'Globe', 'created_at' => $today, 'updated_at' => $today),
            array('mobilePrefix' => '0926', 'network' => 'Globe', 'created_at' => $today, 'updated_at' => $today),
            array('mobilePrefix' => '0927', 'network' => 'Globe', 'created_at' => $today, 'updated_at' => $today),
            array('mobilePrefix' => '0935', 'network' => 'Globe', 'created_at' => $today, 'updated_at' => $today),
            array('mobilePrefix' => '0936', 'network' => 'Globe', 'created_at' => $today, 'updated_at' => $today),
            array('mobilePrefix' => '0937', 'network' => 'Globe', 'created_at' => $today, 'updated_at' => $today),
            array('mobilePrefix' => '0975', 'network' => 'Globe', 'created_at' => $today, 'updated_at' => $today),
            array('mobilePrefix' => '0977', 'network' => 'Globe', 'created_at' => $today, 'updated_at' => $today),
            array('mobilePrefix' => '0994', 'network' => 'Globe', 'created_at' => $today, 'updated_at' => $today),
            array('mobilePrefix' => '0995', 'network' => 'Globe', 'created_at' => $today, 'updated_at' => $today),
            array('mobilePrefix' => '0996', 'network' => 'Globe', 'created_at' => $today, 'updated_at' => $today),
            array('mobilePrefix' => '0997', 'network' => 'Globe', 'created_at' => $today, 'updated_at' => $today),
            array('mobilePrefix' => '0922', 'network' => 'Sun', 'created_at' => $today, 'updated_at' => $today),
            array('mobilePrefix' => '0923', 'network' => 'Sun', 'created_at' => $today, 'updated_at' => $today),
            array('mobilePrefix' => '0924', 'network' => 'Sun', 'created_at' => $today, 'updated_at' => $today),
            array('mobilePrefix' => '0925', 'network' => 'Sun', 'created_at' => $today, 'updated_at' => $today),
            array('mobilePrefix' => '0931', 'network' => 'Sun', 'created_at' => $today, 'updated_at' => $today),
            array('mobilePrefix' => '0932', 'network' => 'Sun', 'created_at' => $today, 'updated_at' => $today),
            array('mobilePrefix' => '0933', 'network' => 'Sun', 'created_at' => $today, 'updated_at' => $today),
            array('mobilePrefix' => '0934', 'network' => 'Sun', 'created_at' => $today, 'updated_at' => $today),
            array('mobilePrefix' => '0942', 'network' => 'Sun', 'created_at' => $today, 'updated_at' => $today),
            array('mobilePrefix' => '0943', 'network' => 'Sun', 'created_at' => $today, 'updated_at' => $today),
        );

        DB::table('telcos')->insert($numbers);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('telcos');
    }
}
