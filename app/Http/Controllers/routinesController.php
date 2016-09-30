<?php

namespace App\Http\Controllers;

/*use Illuminate\Http\Request;

use App\Http\Requests;*/

use Illuminate\Support\Facades\DB;

// use \App\Company as Company;
// use \App\Raw_records as RawRecords;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;

class routinesController extends Controller
{
    /* being ran every minute on weekdays; Will dump into json file. */
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

		if (!isset($data['as_of']))
			exit("No data in upstream.");

        $asOf = $data['as_of'];
        preg_match("/(\d{4}-\d{2}-\d{2})/", $asOf, $match);
        $asOfDateOnly = $match[0];
        preg_match("/(\d{2}:\d{2}:\d{2})/", $asOf, $match);
        $asOfTimeOnly = $match[0];

        // now we want replace ":" to "_"
        $pattern = '/:/';
        $replacement = '_';
        $formatedTime = preg_replace($pattern, $replacement, $asOfTimeOnly);
        file_put_contents("/tmp/pse_monitor/raw_data/{$asOfDateOnly}T{$formatedTime}.json", json_encode($data));
    }

    private function createOrUpdateCompany(Array $stock) {
        // Company::updateOrCreate(['companyName' => $stock['name'], 'symbol' => $stock['symbol'], 'created_at' => date('Y-m-d H:i:s')]);
        DB::insert("INSERT INTO companies(companyName, symbol, created_at) VALUES(?, ?, now())
                ON DUPLICATE KEY UPDATE companyName = ?, symbol = ?", [$stock['name'], $stock['symbol'], $stock['name'], $stock['symbol']]);
    }

    /* Will insert new record or update if record already exists. */
    public function harvestDownloadedCompaniesAndPrices() {
        foreach (glob("/tmp/pse_monitor/raw_data/*.json") as $filename) {
            $data = json_decode(file_get_contents($filename), TRUE);    // returns assoc array
            $stocks = $data['stock'];
            $asOf = $data['as_of'];
            preg_match("/(\d{4}-\d{2}-\d{2})/", $asOf, $match);
            $asOfDateOnly = $match[0];
            preg_match("/(\d{2}:\d{2}:\d{2})/", $asOf, $match);
            $asOfTimeOnly = $match[0];
            $asOfDateTime = $asOfDateOnly . " " . $asOfTimeOnly;
            $asOfDateTime = \DateTime::createFromFormat('Y-m-d H:i:s', $asOfDateTime);
            
            foreach ($stocks as $stock) {
                $this->createOrUpdateCompany($stock);

                /*RawRecords::updateOrCreate([
                    'symbol' => $stock['symbol'],
                    'amount' => $stock['price']['amount'],
                    'percentChange' => $stock['percent_change'],
                    'volume' => $stock['volume'],
                    'asOf' => $asOfDateTime,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);*/

                DB::insert("INSERT INTO raw_records(symbol, amount, percentChange, volume, asOf, created_at) VALUES(?, ?, ?, ?, ?, now())
                    ON DUPLICATE KEY UPDATE symbol = ?, amount = ?, percentChange = ?, volume = ?, asOf = ?",
                    [$stock['symbol'], $stock['price']['amount'], $stock['percent_change'], $stock['volume'], $asOfDateTime,
                    $stock['symbol'], $stock['price']['amount'], $stock['percent_change'], $stock['volume'], $asOfDateTime]);
            }

            $basename = basename($filename);
            rename($filename, "/tmp/pse_monitor/raw_data/processed/$basename");
        }
    }

    public function materializeRawDataPerMinute() {
    	// $rawRecords = RawRecords::whereRaw('materialized IS NULL OR materialized = 0')->get();
    	$rawRecords = DB::select("SELECT id, symbol, amount, percentChange, volume, asOf FROM raw_records WHERE materialized IS NULL OR materialized = 0");

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

    // we're getting issues here with the site.
    public function materializeForPerCompanyDaily() {
        // we only allow 
        $currentDateTime = new \DateTime(date("Y-m-d H:i:s"));  //today
        $pm_trade_end = new \DateTime(date("Y-m-d 16:00:00"));

        if ($currentDateTime < $pm_trade_end)
            exit("This should be ran at 4 PM after trading hours.");

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
    			$uri = "https://www.bloomberg.com/quote/$symbolRow->symbol:PM";
    			$client = new Client();
    			$response = $client->request('GET', $uri, [
    				'headers' => [
    					'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/53.0.2785.116 Safari/537.36',
    					// 'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                        // 'Cookie' => 'bb-mini-player-viewed=true; bbAbVisits=; __gads=ID=e4d088ca3fbde6f9:T=1474447822:S=ALNI_MZrQYXz-Cm4EhD2tciJhP11SCxqQw; ak_rg=US; ak_co=US; bb_country=US|1475675877447; agent_id=6886ea54-bd54-47ac-9fa0-af1f782379c2; session_id=3f55bf8f-2fa0-47ca-9143-197709664653; session_key=2e2b83888480a19f7c3f2b2ff65fa1b1e03a6823; PX_19=SJ08; MARKETS_AP=J10; SRV=JPX02; _gat_UA-11413116-1=1; _gat=1; _bloomberg_on_rails_session=BAh7BjoPc2Vzc2lvbl9pZCIlYzhmODMzNzNjZjZhMDZhN2Y2YWI5OWRlMTg4YzA3NTI%3D--4c93876d084ab1539a239de76dcce1002c60614b; com.bloomberg.player.captions.enabled=false; _tb_sess_r=; _gat__pm_ga=1; __uzma=eeb8e22c-7894-be9e-81e2-c58fd73019c2; __uzmb=1474447812; __uzmc=369227348483; __uzmd=1475141196; bdfpc=004.1587183901.1474447825896; _ga=GA1.2.2077631926.1474447823; _tb_t_ppg=http%3A//www.bloomberg.com/quote/2GO%3APM; _bizo_bzid=90e08c3b-7c52-4ff0-8579-cc1cabdf6abd; _bizo_cksm=D3D347AF645DBD13; _bizo_np_stats=14%3D1203%2C',
                        // 'Host' => 'www.bloomberg.com',
    				]
    			]);

				// $response = $client->get($uri);
				// file_put_contents("/tmp/bloomberg.html", $response->getBody());
				// $file_path = '/tmp/bloomberg.html';
    			// $response_string = file_get_contents($file_path);

    			$response_string = $response->getBody();
    			file_put_contents("/tmp/pse_monitor/html_files/$symbolRow->symbol." . date("Y-m-d") . ".txt", $response_string);
                // exit();
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
