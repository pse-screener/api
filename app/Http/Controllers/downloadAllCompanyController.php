<?php

namespace App\Http\Controllers;

/*use Illuminate\Http\Request;

use App\Http\Requests;*/

use \App\Company;
use \App\Prices;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;

class downloadAllCompanyController extends Controller
{
    public function index() {
    	$client = new Client(); //GuzzleHttp\Client
		$response = $client->get('http://phisix-api4.appspot.com/stocks.json');
		$stocks = json_decode($response->getBody(), TRUE)['stock'];
		// file_put_contents("/tmp/response.txt", print_r($stocks, TRUE));

		foreach ($stocks as $stock) {
			\App\Company::updateOrCreate(['companyName' => $stock['name'], 'symbol' => $stock['symbol']]);
		}		
    }
}
