<?php

namespace App\Http\Controllers;

/*use Illuminate\Http\Request;

use App\Http\Requests;*/

use Illuminate\Support\Facades\DB;

use \App\Company as Company;
use \App\Price as Price;
use \App\System_settings as Settings;
use \App\Raw_records as RawRecords;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;

class companyController extends Controller
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

    private function getCompanyId($symbol) {
    	return DB::table('companies')->where('symbol', $symbol)->value('id');
    }

    private function getLatestClosedDate() {
    	$value = DB::table('system_settings')->where('id', "=", 1);
    	file_put_contents("/tmp/value.txt", print_r($value, TRUE));
    	return $value;
    }

    public function downloadPrices_Old() {
    	$client = new Client(); //GuzzleHttp\Client

    	// courtesy of http://phisix-api4.appspot.com/
		$response = $client->get('http://phisix-api4.appspot.com/stocks.json');
		$data = json_decode($response->getBody(), TRUE);
		
		// file_put_contents("/tmp/response.txt", print_r($data, TRUE));
		$stocks = $data['stock'];
		$asOf = $data['as_of'];
		preg_match("/(\d{4}-\d{2}-\d{2})/", $asOf, $match);
		$asOfDateOnly = $match[0];
		preg_match("/(\d{2}:\d{2}:\d{2})/", $asOf, $match);
		$asOfTimeOnly = $match[0];
		$asOfDateTime = $asOfDateOnly . " " . $asOfTimeOnly;

		$asOfDateTime = \DateTime::createFromFormat('Y-m-d H:i:s', $asOfDateTime);

		$latestClosedDateOnly = \DateTime::createFromFormat('Y-m-d', $this->getLatestClosedDate());
		$asOfDateOnly = \DateTime::createFromFormat('Y-m-d', $asOfDateOnly);
		
		if ($latestClosedDateOnly < $asOfDateOnly) {
			// opening price

			foreach ($stocks as $stock) {
				$companyId = $this->getCompanyId($stock['symbol']);
				$price = $stock['price']['amount'];
				$percentChange = $stock['percent_change'];
				$volume = $stock['volume'];

				Price::create([
					'companyId' => $companyId,
					'open' => $price,
					'high' => $price,
					'low' => $price,
					'close' => $price,
					'tsOpen' => $asOfDateTime,
					'tsHigh' => $asOfDateTime,
					'tsLow' => $asOfDateTime,
					'tsClose' => $asOfDateTime,
					'percentChange' => $percentChange,
					'volume' => $volume,
				]);
			}
		} elseif ($latestClosedDateOnly == $asOfDateOnly) {
			// high, low, closing
			foreach ($stocks as $stock) {
				// DB::insert('insert into companies () values (?, ?)', [1, 'Dayle']);
				DB::statement("call update_companies($companyId, $price, $percentChange, $volume)");
			}
		} else {
			echo "Something wrong here.";
		}

		// Settings::where('id', 1)->update(['latest_tsPrice' => $asOfDateOnly]);
    }

    /*public function dlAllCompaniesAndClosePrice() {
    	$client = new Client(); //GuzzleHttp\Client

    	// http://phisix-api4.appspot.com/
		$response = $client->get('http://phisix-api4.appspot.com/stocks.json');
		$data = json_decode($response->getBody(), TRUE);
		
		// file_put_contents("/tmp/response.txt", print_r($data, TRUE));
		$stocks = $data['stock'];
		$asOf = $data['as_of'];
		preg_match("/(\d{4}-\d{2}-\d{2})/", $asOf, $match);
		$date = $match[0];
		preg_match("/(\d{2}:\d{2}:\d{2})/", $asOf, $match);
		$time = $match[0];
		$dateTime = $date . " " . $time;

		$asOf = \DateTime::createFromFormat('Y-m-d H:i:s', $dateTime);

		foreach ($stocks as $stock) {
			$companyObjData = Company::updateOrCreate(['companyName' => $stock['name'], 'symbol' => $stock['symbol']]);

			Price::updateOrCreate([
				'companyId' => $companyObjData->id,
				'close' => $stock['price']['amount'],
				'tsClose' => $asOf,
				'percentChange' => $stock['percent_change'],
				'volume' => $stock['volume'],
			]);
		}
    }*/

    /* being ran every minute. */
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
			RawRecords::create([
				'symbol' => $stock['symbol'],
				'companyName' => $stock['name'],
				'amount' => $stock['price']['amount'],
				'percentChange' => $stock['percent_change'],
				'volume' => $stock['volume'],
				'asOf' => $asOfDateTime,
			]);
		}
    }

    private function getLastPerMinuteBuild() {
    	return DB::table('system_settings')->where('id', 1)->value('last_per_min_build');
    }

    public function materializeRawDataPerMinute() {
    	$lastPerMinuteBuild = $this->getLastPerMinuteBuild();
    	if (!$lastPerMinuteBuild)
    		echo "Error: No last per minute build value.";

    	$rawRecords = RawRecords::where("asOf", "=", "$lastPerMinuteBuild")->get();
    	$materlializedRecords = array();

    	foreach ($rawRecords as $rawRecord) {
    		$symbol = $rawRecord['symbol'];
    		$price = $rawRecord['amount'];
    		$asOf = $rawRecord['asOf'];
    		$percentChange = $rawRecord['percentChange'];
    		$volume = $rawRecord['volume'];

    		DB::statement("call sp_aggregate_per_minute($symbol, $price, $asOf, $percentChange, $volume)");	
    		$materlializedRecords[] = $rawRecord['id'];
    	}

    	foreach ($materlializedRecords as $record) {
    		# code...
    	}

    	// DB::statement("UPDATE system_settings SET last_per_min_build = DATE_ADD(last_per_min_build, INTERVAL 1 MINUTE) WHERE id = 1");
    }
}
