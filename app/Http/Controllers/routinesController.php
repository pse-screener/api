<?php

namespace App\Http\Controllers;

/*use Illuminate\Http\Request;

use App\Http\Requests;*/

use Illuminate\Support\Facades\DB;

use \App\Company as Company;
use \App\Raw_records as RawRecords;
use \App\Materialize_per_company_daily as MaterializePerCompanyDaily;
// use \App\Aggregate_per_minute as AggregatePerMinute;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;

class routinesController extends Controller
{
	/* This will insert new record or update if record already exists. */
	public function downloadAllCompanies() {
    	$client = new Client(); //GuzzleHttp\Client

    	// http://phisix-api4.appspot.com/
		$response = $client->get('http://phisix-api4.appspot.com/stocks.json');
		$data = json_decode($response->getBody(), TRUE);

		$stocks = $data['stock'];

		foreach ($stocks as $stock) {
			Company::updateOrCreate(['companyName' => $stock['name'], 'symbol' => $stock['symbol']]);
		}
    }

    /* being ran every minute on weekdays. Will insert new record or update if record already exists. */
    public function downloadPrices() {
    	$client = new Client();

		$response = $client->get('http://phisix-api4.appspot.com/stocks.json');
		$data = json_decode($response->getBody(), TRUE);
		
		$stocks = $data['stock'];
		$asOf = $data['as_of'];
		preg_match("/(\d{4}-\d{2}-\d{2})/", $asOf, $match);
		$asOfDateOnly = $match[0];
		preg_match("/(\d{2}:\d{2}:\d{2})/", $asOf, $match);
		$asOfTimeOnly = $match[0];
		$asOfDateTime = $asOfDateOnly . " " . $asOfTimeOnly;
		$asOfDateTime = \DateTime::createFromFormat('Y-m-d H:i:s', $asOfDateTime);
		
		foreach ($stocks as $stock) {
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
    	
    	foreach ($rawRecords as $rawRecord) {
    		$symbol = $rawRecord['symbol'];
    		$price = $rawRecord['amount'];
    		$asOf = $rawRecord['asOf'];
    		$percentChange = $rawRecord['percentChange'];
    		$volume = $rawRecord['volume'];
    		$rawRecordId = $rawRecord['id'];

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

    		$date1 = $tableDate->asOf;

    		$records = DB::table('aggregate_per_minute')->whereRaw("DATE_FORMAT(asOf, '%Y-%m-%d') = '$date1'")->get();    		

    		foreach ($records as $row) {
    			$myDate = $row->asOf;

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
				$anotherRecords = DB::select($sql);

				// file_put_contents("/tmp/records.txt", print_r($sql, TRUE));

				// exit();

				foreach ($anotherRecords as $anotherRecord) {
					MaterializePerCompanyDaily::updateOrCreate([
						'companyId' => $anotherRecord->companyId,
						'openPrice' => $anotherRecord->openPrice,
						'highPrice' => $anotherRecord->highPrice,
						'lowPrice' => $anotherRecord->lowPrice,
						'closePrice' => $anotherRecord->closePrice,
						'tsOpen' => $anotherRecord->tsOpen,
						'tsHigh' => $anotherRecord->tsHigh,
						'tsLow' => $anotherRecord->tsLow,
						'tsClose' => $anotherRecord->tsClose,
						'asOf' => $myDate,
					]);
				}
    		}
    	}

    }
}