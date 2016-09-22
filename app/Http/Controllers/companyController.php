<?php

namespace App\Http\Controllers;

/*use Illuminate\Http\Request;

use App\Http\Requests;*/

use Illuminate\Support\Facades\DB;

use \App\Company as Company;
use \App\Price as Price;

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
    	// return Company::where('symbol', '=', $symbol)[0];
    	return DB::table('companies')->where('symbol', $symbol)->value('id');
    }

    private function getLatestClosedDate() {
    	return DB::table('companies')->max('tsClose')->value('tsClose');
    }

    public function downloadPrice() {
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

				Price::create([
					'companyId' => $companyId,
					'open' => $stock['price']['amount'],
					'high' => $stock['price']['amount'],
					'low' => $stock['price']['amount'],
					'close' => $stock['price']['amount'],
					'tsOpen' => $asOfDateTime,
					'tsHigh' => $asOfDateTime,
					'tsLow' => $asOfDateTime,
					'tsClose' => $asOfDateTime,
					'percentChange' => $stock['percent_change'],
					'volume' => $stock['volume'],
				]);
			}
		} elseif ($latestClosedDateOnly == $asOfDateOnly) {
			// high, low, closing
			foreach ($stocks as $stock) {
				// DB::insert('insert into companies () values (?, ?)', [1, 'Dayle']);
				DB::
			}
		}
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

}
