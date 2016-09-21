<?php

namespace App\Http\Controllers;

/*use Illuminate\Http\Request;

use App\Http\Requests;*/

use \App\Company as Company;
use \App\Price as Price;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;

class companyController extends Controller
{
    public function dlAllCompaniesAndClosePrice() {
    	$client = new Client(); //GuzzleHttp\Client
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
				'closePercentChange' => $stock['percent_change'],
				'closeVolume' => $stock['volume'],
			]);
		}
    }
}
