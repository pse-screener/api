<?php

namespace App\Http\Controllers;

/*use Illuminate\Http\Request;

use App\Http\Requests;*/

use Illuminate\Support\Facades\DB;

use \App\Company as Company;
use \App\Raw_records as RawRecords;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;

class routinesController extends Controller
{
	/* This will insert new record or update if record already exists. */
	/*public function downloadAllCompanies() {
    	$client = new Client(); //GuzzleHttp\Client

    	// http://phisix-api4.appspot.com/
		$response = $client->get('http://phisix-api4.appspot.com/stocks.json');
		$data = json_decode($response->getBody(), TRUE);

		if (!isset($data['as_of'])) {
			exit();
		}

		$stocks = $data['stock'];

		foreach ($stocks as $stock) {
			Company::updateOrCreate(['companyName' => $stock['name'], 'symbol' => $stock['symbol']]);
		}
    }*/

    /* being ran every minute on weekdays. Will insert new record or update if record already exists. */
    public function downloadCompaniesAndPrices() {
    	$client = new Client();

		$response = $client->get('http://phisix-api4.appspot.com/stocks.json');
		$data = json_decode($response->getBody(), TRUE);

		if (!isset($data['as_of'])) {
			exit();
		}
		
		$stocks = $data['stock'];
		$asOf = $data['as_of'];
		preg_match("/(\d{4}-\d{2}-\d{2})/", $asOf, $match);
		$asOfDateOnly = $match[0];
		preg_match("/(\d{2}:\d{2}:\d{2})/", $asOf, $match);
		$asOfTimeOnly = $match[0];
		$asOfDateTime = $asOfDateOnly . " " . $asOfTimeOnly;
		$asOfDateTime = \DateTime::createFromFormat('Y-m-d H:i:s', $asOfDateTime);

		file_put_contents("/tmp/downloadCompaniesAndPrices.txt", date('Y-m-d H:i:s'));
		
		foreach ($stocks as $stock) {
			Company::updateOrCreate(['companyName' => $stock['name'], 'symbol' => $stock['symbol']]);

			RawRecords::updateOrCreate([
				'symbol' => $stock['symbol'],
				'amount' => $stock['price']['amount'],
				'percentChange' => $stock['percent_change'],
				'volume' => $stock['volume'],
				'asOf' => $asOfDateTime,
			]);
		}
    }

    public function materializeRawDataPerMinute() {
    	$rawRecords = RawRecords::whereRaw('materialized IS NULL OR materialized = 0')->get();

    	// file_put_contents("/tmp/materializeRawDataPerMinute.txt", date('Y-m-d H:i:s'));
    	// file_put_contents("/tmp/materializeRawDataPerMinute.txt", print_r($rawRecords, TRUE));
    	// file_put_contents("/tmp/materializeRawDataPerMinute.txt", count($rawRecords));
    	
    	
    	foreach ($rawRecords as $rawRecord) {
    		// file_put_contents("/tmp/materializeRawDataPerMinute.txt", "OKay." . $rawRecord->symbol);	
    		$symbol = $rawRecord->symbol;
    		$price = $rawRecord->amount;
    		$asOf = $rawRecord->asOf;
    		$percentChange = $rawRecord->percentChange;
    		$volume = $rawRecord->volume;
    		$rawRecordId = $rawRecord->id;

    		DB::statement("call sp_aggregate_per_minute('$symbol', $price, '$asOf', $percentChange, $volume, $rawRecordId)");
    	}
    }

    public function materializeForPerCompanyDaily() {
    	$sql = "SELECT DATE_FORMAT(asOf, '%Y-%m-%d') AS asOf FROM aggregate_per_minute 
    		WHERE materialized IS NULL OR materialized = 0
    		GROUP BY DATE_FORMAT(asOf, '%Y-%m-%d')
    		ORDER BY DATE_FORMAT(asOf, '%Y-%m-%d')";

    	$tableDates = DB::select($sql);

    	foreach ($tableDates as $tableDate) {
    		// group by dates

    		$myDate = $tableDate->asOf;

			$sql = "SELECT companyId,
				(SELECT price FROM aggregate_per_minute
					WHERE DATE_FORMAT(asOf, '%Y-%m-%d') = DATE_FORMAT('$myDate', '%Y-%m-%d')
					AND companyId = table1.companyId
					ORDER BY asOf ASC LIMIT 1
				) AS openPrice,

				MAX(price) AS highPrice, MIN(price) AS lowPrice,

				(SELECT price FROM aggregate_per_minute
					WHERE DATE_FORMAT(asOf, '%Y-%m-%d') = DATE_FORMAT('$myDate', '%Y-%m-%d')
					AND companyId = table1.companyId
					ORDER BY asOf DESC LIMIT 1
				) AS closePrice,
				(SELECT asOf FROM aggregate_per_minute
					WHERE DATE_FORMAT(asOf, '%Y-%m-%d') = DATE_FORMAT('$myDate', '%Y-%m-%d')
					AND companyId = table1.companyId
					ORDER BY asOf ASC LIMIT 1
				) AS tsOpen,
				(SELECT asOf  FROM aggregate_per_minute
					WHERE DATE_FORMAT(asOf, '%Y-%m-%d') = DATE_FORMAT('$myDate', '%Y-%m-%d')
					AND companyId = table1.companyId
					GROUP BY asOf
					ORDER BY MAX(price) DESC LIMIT 1
				) AS tsHigh,
				(SELECT asOf  FROM aggregate_per_minute
					WHERE DATE_FORMAT(asOf, '%Y-%m-%d') = DATE_FORMAT('$myDate', '%Y-%m-%d')
					AND companyId = table1.companyId
					GROUP BY asOf
					ORDER BY MIN(price) ASC LIMIT 1
				) AS tsLow,
				(SELECT asOf FROM aggregate_per_minute
					WHERE DATE_FORMAT(asOf, '%Y-%m-%d') = DATE_FORMAT('$myDate', '%Y-%m-%d')
					AND companyId = table1.companyId
					ORDER BY asOf DESC LIMIT 1
				) AS tsClose
				FROM aggregate_per_minute AS table1
				WHERE DATE_FORMAT(asOf, '%Y-%m-%d') = DATE_FORMAT('$myDate', '%Y-%m-%d')
				GROUP BY companyId";
			$rows = DB::select($sql);

			foreach ($rows as $row) {
				$companyId = $row->companyId;
				$openPrice = $row->openPrice;
				$highPrice = $row->highPrice;
				$lowPrice = $row->lowPrice;
				$closePrice = $row->closePrice;
				$tsOpen = $row->tsOpen;
				$tsHigh = $row->tsHigh;
				$tsLow = $row->tsLow;
				$tsClose = $row->tsClose;
				$asOf = $myDate;

				/*$id = DB::insert("INSERT INTO materialize_per_company_daily(companyId, openPrice, highPrice, lowPrice, closePrice, tsOpen, tsHigh, tsLow, tsClose, asOf)
					VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
					ON DUPLICATE KEY UPDATE openPrice = ?, highPrice = ?, lowPrice = ?, closePrice = ?, tsOpen = ?, tsHigh = ?, tsLow = ?, tsClose = ?",
					[$companyId, $openPrice, $highPrice, $lowPrice, $closePrice, $tsOpen, $tsHigh, $tsLow, $tsClose, $asOf,
						$openPrice, $highPrice, $lowPrice, $closePrice, $tsOpen, $tsHigh, $tsLow, $tsClose
					]);*/
				DB::statement("call sp_materialize_per_company_daily($companyId, $openPrice, $highPrice, $lowPrice, $closePrice, '$tsOpen', '$tsHigh', '$tsLow', '$tsClose', '$asOf')");
			}
    	}
    }

    public function performEOD() {
    	// we have to check the time first before we execute this.
    	DB::statement("call sp_perform_eod()");
    }
}
