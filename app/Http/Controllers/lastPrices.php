<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;

class lastPrices extends Controller
{
	/* gets company price by a company. */
    public function getLastPrice($companyId = 1, $date = null) {
    	$companyPrices = [];
    	$companyPrices = \App\Company::find($companyId);

    	if ($companyPrices)
    		$companyPrices->prices()->where('asOf', '2017-04-26')->get();

    	return response()->json($companyPrices);
    }

    /* Gets company price by companies. */
    public function getLastPrices() {
    	$lastDate = DB::table('materialize_per_company_daily')->max('asOf'); // $lastDate is the latest date.

    	$lastPrices = DB::table('companies')
    					->join('materialize_per_company_daily as mpcd', 'companies.id', '=', 'mpcd.companyId')
    					->select('companyName', 'symbol', 'price', 'asOf')
    					->where('mpcd.asOf', $lastDate)
    					->get();

    	return response()->json($lastPrices);
    }
}
