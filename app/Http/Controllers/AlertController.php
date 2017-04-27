<?php

namespace App\Http\Controllers;

use Validator;

use Illuminate\Http\Request;

use Illuminate\Contracts\Auth\Guard;

use Illuminate\Support\Facades\DB;

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

    private function checkData($request)
    {
        $request["price"] = str_replace(',', '' , $request->price); // remove commas on price.

        $this->validate($request, [
            'companyId' => 'bail|required|max:3',
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
        $subscriptions = \App\Subscriptions::whereRaw("DATE_FORMAT(validUntil, '%Y-%m-%d') >= DATE_FORMAT(NOW(), '%Y-%m-%d')")
                        ->select('id')
                        ->where("userId", $user->id)
                        ->get();

        return $subscriptions;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $subscriptions = $this->checkData($request);

        if ($subscriptions->count() == 0)
            return response()->json(["code" => 1, "message" => "No active subscription."]);

        // we only need the first record no matter how many are active subscription.
        $alertCount =\App\Alerts::where('subscriptionId', $subscriptions[0]->id)->count();
        if ($alertCount >= 15)
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

            break;  // no matter how many subscription, we only need 1 entry.
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

        $subscriptions = $this->checkData($request);
        
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
