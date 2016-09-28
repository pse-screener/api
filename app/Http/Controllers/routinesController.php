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
    private function createOrUpdateCompany($stock) {
    	DB::insert("INSERT INTO companies(companyName, symbol, created_at) VALUES(?, ?, now())
				ON DUPLICATE KEY UPDATE companyName = ?, symbol = ?", [$stock['name'], $stock['symbol'], $stock['name'], $stock['symbol']]);
    }

    /* being ran every minute on weekdays. Will insert new record or update if record already exists. */
    public function downloadCompaniesAndPrices() {
    	if (!config('app.download_raw_data_beyond_trading_window')) {
    		if (date("N") > 5) {
    			exit("Environment doesn't allow download raw data beyong trading hours.");
    		}

    		$currentDateTime = new \DateTime(date("Y-m-d H:i:s"));	//today
    		// note: give allowance.
    		$am_trade_start = new \DateTime(date("Y-m-d 09:29:00"));
    		$am_trade_end = new \DateTime(date("Y-m-d 12:02:00"));
    		$pm_trade_start = new \DateTime(date("Y-m-d 01:29:00"));
    		$pm_trade_end = new \DateTime(date("Y-m-d 15:32:00"));

    		if (!($currentDateTime >= $am_trade_start && $currentDateTime <= $am_trade_end) || !($currentDateTime >= $pm_trade_start && $currentDateTime <= $pm_trade_end)) {
    			exit("Environment doesn't allow download raw data beyong trading hours.");
    		}
    	}

    	$client = new Client();

		$response = $client->get('http://phisix-api4.appspot.com/stocks.json');
		$data = json_decode($response->getBody(), TRUE);

		if (!isset($data['as_of'])) {
			exit("No data in upstream.");
		}
		
		$stocks = $data['stock'];
		$asOf = $data['as_of'];
		preg_match("/(\d{4}-\d{2}-\d{2})/", $asOf, $match);
		$asOfDateOnly = $match[0];
		preg_match("/(\d{2}:\d{2}:\d{2})/", $asOf, $match);
		$asOfTimeOnly = $match[0];
		$asOfDateTime = $asOfDateOnly . " " . $asOfTimeOnly;
		$asOfDateTime = \DateTime::createFromFormat('Y-m-d H:i:s', $asOfDateTime);
		
		foreach ($stocks as $stock) {
			// Company::updateOrCreate(['companyName' => $stock['name'], 'symbol' => $stock['symbol']]);

			$this->createOrUpdateCompany($stock);

			/*RawRecords::updateOrCreate([
				'symbol' => $stock['symbol'],
				'amount' => $stock['price']['amount'],
				'percentChange' => $stock['percent_change'],
				'volume' => $stock['volume'],
				'asOf' => $asOfDateTime,
			]);*/

			DB::insert("INSERT INTO raw_records(symbol, amount, percentChange, volume, asOf, created_at) VALUES(?, ?, ?, ?, ?, now())
				ON DUPLICATE KEY UPDATE symbol = ?, amount = ?, percentChange = ?, volume = ?, asOf = ?",
				[$stock['symbol'], $stock['price']['amount'], $stock['percent_change'], $stock['volume'], $asOfDateTime,
				$stock['symbol'], $stock['price']['amount'], $stock['percent_change'], $stock['volume'], $asOfDateTime]);
		}
    }

    public function materializeRawDataPerMinute() {
    	// $rawRecords = RawRecords::whereRaw('materialized IS NULL OR materialized = 0')->get();
    	$rawRecords = DB::select("SELECT id, symbol, amount, percentChange, volume, asOf FROM raw_records WHERE materialized IS NULL OR materialized = 0");
    	
    	$counter = 0;
    	foreach ($rawRecords as $rawRecord) {
    		$rawRecordId = $rawRecord->id;
    		$symbol = $rawRecord->symbol;
    		$price = $rawRecord->amount;
    		$percentChange = $rawRecord->percentChange;
    		$volume = $rawRecord->volume;
    		$asOf = $rawRecord->asOf;

    		DB::insert("call sp_aggregate_per_minute('$symbol', $price, '$asOf', $percentChange, $volume, $rawRecordId)");
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

    		$sql = "SELECT id FROM aggregate_per_minute WHERE DATE_FORMAT(asOf, '%Y-%m-%d') = '$myDate'";
    		$aggPerMinuteIds = db::select($sql);

    		foreach ($aggPerMinuteIds as $aggPerMinuteId)
    			$aggPerMinuteArrayIds[] = $aggPerMinuteId->id;

    		$aggPerMinuteStringIds = implode(",", $aggPerMinuteArrayIds);

    		$sql = "SELECT companies.symbol FROM companies JOIN aggregate_per_minute ON companies.id = aggregate_per_minute.companyId
					WHERE DATE_FORMAT(asOf, '%Y-%m-%d') = '$myDate' AND (materialized IS NULL OR materialized = 0)
					GROUP BY companies.symbol";
    		$symbolRows = db::select($sql);

    		foreach ($symbolRows as $symbolRow) {
    			$uri = "http://www.bloomberg.com/quote/$symbolRow->symbol:PM";
    			$client = new Client();
    			$response = $client->request('GET', $uri, [
    				'headers' => [
    					'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/53.0.2785.116 Safari/537.36',
    					'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
    				]
    			]);

				// $response = $client->get($uri);
				// file_put_contents("/tmp/bloomberg.html", $response->getBody());
				// $file_path = '/tmp/bloomberg.html';
    			// $response_string = file_get_contents($file_path);

    			$response_string = $response->getBody();
    			file_put_contents("/tmp/pse_monitor/html_files/$symbolRow->symbol." . date("Y-m-d") . ".txt", $response_string);
    		}

    		foreach ($symbolRows as $symbolRow) {
    			$file_string = "/tmp/pse_monitor/html_files/$symbolRow->symbol." . date("Y-m-d") . ".txt";
    			$openPrice_regex = "/Open\s*<\/div>\s*<div\s*class=\"cell__value cell__value_\">([^<]*)<\/div>/";
    			$highAndLowPrices_regex = "/Day Range <\/div> <div class=\"cell__value cell__value_\">([^<]*)<\/div>/";
    			$closePrice_regex = "/<div\s*class=\"price\">([^<]*)<\/div>/";
    			$lowPrice_regex = "/([^\-]*)/";
    			$highPrice_regex = "/-(.*)/";

    			preg_match($openPrice_regex, $file_string, $matches);
    			$openPrice = trim($matches[1]);
    			preg_match($highAndLowPrices_regex, $file_string, $matches);
    			$highAndLowPrices = trim($matches[1]);
    			preg_match($closePrice_regex, $file_string, $matches);
    			$closePrice = trim($matches[1]);
    			preg_match($lowPrice_regex, $highAndLowPrices, $matches);
    			$lowPrice = $matches[1];
    			preg_match($highPrice_regex, $highAndLowPrices, $matches);
    			$highPrice = $matches[1];

    			$companyData[] = array(
    					"symbol" => $symbolRow->symbol,
	    				"openPrice" => $openPrice,
	    				"lowPrice" => $lowPrice,
	    				"highPrice" => $highPrice,
	    				"closePrice" => $closePrice,
    			);
    		}

			foreach ($companyData as $row) {
				$symbol = $row['symbol'];
				$openPrice = $row['openPrice'];
				$highPrice = $row['highPrice'];
				$lowPrice = $row['lowPrice'];
				$closePrice = $row['closePrice'];
				$asOf = $myDate;

				DB::statement("call sp_materialize_per_company_daily('$symbol', $openPrice, $highPrice, $lowPrice, $closePrice, '$asOf')");
			}

			$sql = "UPDATE aggregate_per_minute SET materialized = 1 WHERE id IN ($aggPerMinuteStringIds)";
			DB::update($sql);
    	}
    }

    public function performEOD() {
    	DB::statement("call sp_perform_eod()");
    }
}
