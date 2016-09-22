<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUpdatePriceStoredProc extends Migration
{
    /*
        DELIMITER $$

        USE `pse_screener_pmorcilladev`$$

        DROP PROCEDURE IF EXISTS `update_prices`$$

        CREATE DEFINER=`pmorcilladev`@`%` PROCEDURE `update_prices`(
            IN companyId INT(11),
            IN price DECIMAL(16, 4),
            IN percentChange DECIMAL(8, 2),
            IN volume INT(11)
        )
        BEGIN
           SET @high = (SELECT high FROM prices WHERE companyId = companyId);
           SET @low = (SELECT low FROM prices WHERE companyId = companyId);
           IF (price > @high) THEN
              UPDATE prices SET high = price, `close` = price, percentChange = percentChange, volume = volume WHERE companyId = companyId;
           ELSEIF (price < @low) THEN
              UPDATE prices SET low = price, `close`= price, percentChange = percentChange, volume = volume WHERE companyId = companyId;
           END IF;
        END$$

        DELIMITER ;
    */

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // sample
        // DB::unprepared('CREATE PROCEDURE test() BEGIN SELECT * FROM user; END');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
