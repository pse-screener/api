<?php

namespace App\Http\Controllers;

use Validator;

use Illuminate\Http\Request;

use Illuminate\Contracts\Auth\Guard;

use Illuminate\Support\Facades\DB;

use \App\Helpers;

class AlertController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    private function checkSubscription($request)
    {
        $request["price"] = str_replace(',', '' , $request->price); // remove commas on price.

        $this->validate($request, [
            'companyId' => 'bail|required',
            'priceCondition' => 'required|in:MA,MB',
            'price' => 'required|numeric',
        ]);

        if ($request->priceCondition == "MA")
            $request->priceCondition = "movesAbove";
        elseif ($request->priceCondition == "MB")
            $request->priceCondition = "movesBelow";
        else
            exit(); //we don't allow though this has been checked in validate() above.

        $user = \Auth::user();
        /*$subscriptions = \App\Subscriptions::whereRaw("DATE_FORMAT(validUntil, '%Y-%m-%d') >= DATE_FORMAT(NOW(), '%Y-%m-%d')")
                        ->select('id')
                        ->where("userId", $user->id)
                        ->get();*/
        $subscriptions = \App\Subscriptions::select('id')->where("userId", $user->id)->get();
        return $subscriptions;
    }

    private function checkLastClosedPrice($companyId) {
        $lastClosedDate = DB::table('materialize_per_company_daily')->max('asOf');

        $lastClosedPrice = DB::table('companies')
                ->join('materialize_per_company_daily as mpcd', 'companies.id', '=', 'mpcd.companyId')
                ->select('price')
                ->where('mpcd.asOf', $lastClosedDate)
                ->where('companyId', $companyId)
                ->orderBy('companies.companyName')
                ->first();


        if ($lastClosedPrice) {
            return $lastClosedPrice;
        } else {    // sometimes the query doesn't return record so I set it to 0.00
            $lastClosedPrice = new \StdClass;
            $lastClosedPrice->price = "0.00";
            return $lastClosedPrice;
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $subscriptions = $this->checkSubscription($request);

        if ($subscriptions->count() == 0)
            return response()->json(["code" => 1, "message" => "No active subscription."]);

        // now compare the prices
        $lastClosedPrice = $this->checkLastClosedPrice($request->companyId);

        if ($request->priceCondition == "movesBelow") {
            if (($request->price > $lastClosedPrice->price) && ($lastClosedPrice->price != "0.00"))
                return response()->json(["code" => 1, "message" => "Last closed price $lastClosedPrice->price is already below your alert price."]);
        } elseif ($request->priceCondition == "movesAbove") {
            if (($request->price < $lastClosedPrice->price) && ($lastClosedPrice->price != "0.00"))
                return response()->json(["code" => 1, "message" => "Last closed price $lastClosedPrice->price is already above your alert price."]);
        } else {
            return response()->json(["code" => 1, "message" => "Unknown price condition."]);
        }

        // we only need the first record no matter how many are active subscription.
        $alertCount =\App\Alerts::where('subscriptionId', $subscriptions[0]->id)->count();
        if ($alertCount >= 10)
            return response()->json(["code" => 1, "message" => "Maximum limits of alerts have been reached."]);
        
        foreach ($subscriptions as $subscription) {
            $alert =  \App\Alerts::firstOrNew([
                'companyId' => $request->companyId,
                'priceCondition' => $request->priceCondition,
                'subscriptionId' => $subscription->id
            ]);
            $alert->subscriptionId = $subscription->id;
            $alert->companyId = $request->companyId;
            $alert->priceCondition = $request->priceCondition;
            $alert->price = $request->price;
            $alert->sendSms = 1;
            $alert->sendEmail = 1;

            $alert->save();

            break;  // no matter how many subscriptions, we only need 1 entry.
        }

        return response()->json(["code" => 0, "message" => "Successful."]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'symbol' => 'bail|required',
        ]);

        $companyId = \App\Company::where('symbol', $request->symbol)->select('id')->get()[0]['id'];
        if(!$companyId)
            return response()->json(["code" => 1, "message" => "Company id not found."]);

        $request->request->add(['companyId' => $companyId]);

        $subscriptions = $this->checkSubscription($request);
        $lastClosedPrice = $this->checkLastClosedPrice($companyId);

        if ($request->priceCondition == "movesBelow") {
            if ($request->price > $lastClosedPrice->price)
                return response()->json(["code" => 1, "message" => "Last closed price $lastClosedPrice->price is already below your alert price."]);
        } elseif ($request->priceCondition == "movesAbove") {
            if ($request->price < $lastClosedPrice->price)
                return response()->json(["code" => 1, "message" => "Last closed price $lastClosedPrice->price is already above your alert price."]);
        } else {
            return response()->json(["code" => 1, "message" => "Unknown price condition."]);
        }
        
        foreach ($subscriptions as $subscription) {
            $alert =  \App\Alerts::find($id);
            $alert->subscriptionId = $subscription->id;
            $alert->companyId = $request->companyId;
            $alert->priceCondition = $request->priceCondition;
            $alert->price = $request->price;
            $alert->sentToSms = 0;
            $alert->sendSms = 1;
            $alert->sendEmail = 1;

            $alert->save();

            break;  // no matter how many subscription, we only need 1 entry.
        }

        return response()->json(["code" => 0, "message" => "Successful"]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  string  $id or string of IDs
     * @return \Illuminate\Http\Response
     */
    public function destroy($alertIDs_string)
    {
        // Soft deletion might be in the roadmap soon but for now, no.

        $alertsArray = explode(',', urldecode($alertIDs_string));
        \App\Alerts::destroy($alertsArray);
        return response()->json(["code" => 0, "message" => "Deleting successful!"]);
    }
}
