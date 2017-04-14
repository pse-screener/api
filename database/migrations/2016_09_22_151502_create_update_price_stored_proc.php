<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUpdatePriceStoredProc extends Migration
{
    /*
        DELIMITER $$

        USE `pse_monitor_pmorcilladev`$$

        DROP PROCEDURE IF EXISTS `sp_aggregate_per_minute`$$

        CREATE DEFINER=`pmorcilladev`@`%` PROCEDURE `sp_aggregate_per_minute`(
            IN var_symbol VARCHAR(255),
            IN var_price DECIMAL(16, 4),
            IN var_asOf DATETIME,
            IN var_percentChange DECIMAL(8, 4),
            IN var_volume INT(11),
            IN rawRecordId INT(11)
        )
        BEGIN
            SET @currentDate = (SELECT DATE_FORMAT(var_asOf, '%Y-%m-%d'));
            SET @companyId = (SELECT id FROM companies WHERE symbol = var_symbol);
            SET @price = (SELECT `price` FROM aggregate_per_minute WHERE companyId = @companyId AND DATE_FORMAT(asOf, '%Y-%m-%d') = @currentDate ORDER BY id DESC LIMIT 1);
            SET @countRows = (SELECT COUNT(@price));
            IF (@countRows <= 0) THEN
                SET @price = 0.00;
            END IF;
            
            IF (var_price != @price) THEN
                INSERT INTO aggregate_per_minute(companyId, price, asOf, percentChange, volume, created_at)
                    VALUES(@companyId, var_price, var_asOf, var_percentChange, var_volume, NOW());
                -- tag as materialized
                UPDATE raw_records SET materialized = 1 WHERE id = rawRecordId;
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
        // to be continued working on this one.
/*        $storedProc = <<<SQL
DELIMITER $$

USE `pse_monitor_pmorcilladev`$$

DROP PROCEDURE IF EXISTS `sp_aggregate_per_minute`$$

CREATE DEFINER=`pmorcilladev`@`%` PROCEDURE `sp_aggregate_per_minute`(
    IN var_symbol VARCHAR(255),
    IN var_price DECIMAL(16, 4),
    IN var_asOf DATETIME,
    IN var_percentChange DECIMAL(8, 4),
    IN var_volume INT(11),
    IN rawRecordId INT(11)
)
BEGIN
    SET @currentDate = (SELECT DATE_FORMAT(var_asOf, '%Y-%m-%d'));
    SET @companyId = (SELECT id FROM companies WHERE symbol = var_symbol);
    SET @price = (SELECT `price` FROM aggregate_per_minute WHERE companyId = @companyId AND DATE_FORMAT(asOf, '%Y-%m-%d') = @currentDate ORDER BY id DESC LIMIT 1);
    SET @countRows = (SELECT COUNT(@price));
    IF (@countRows <= 0) THEN
        SET @price = 0.00;
    END IF;
    
    IF (var_price != @price) THEN
        INSERT INTO aggregate_per_minute(companyId, price, asOf, percentChange, volume, created_at)
            VALUES(@companyId, var_price, var_asOf, var_percentChange, var_volume, NOW());
        -- tag as materialized
        UPDATE raw_records SET materialized = 1 WHERE id = rawRecordId;
    END IF;
END$$

DELIMITER ;
SQL;
        DB::connection()->getPdo()->exec($storedProc);*/
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
