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
        $user = \Auth::user();
        $alerts = DB::table('alerts')
                    ->join('companies', 'alerts.companyId', '=', 'companies.id')
                    ->join('subscriptions', 'alerts.subscriptionId', '=', 'subscriptions.id')
                    ->select('alerts.id', 'symbol', 'companyName', 'priceCondition', 'price')
                    ->where('subscriptions.userId', '=', $user->id)
                    ->get();
        return response()->json($alerts);
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
        str_replace(',', '' , $request->price); // remove commas on price.

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

        if (!count($subscriptions))
            return response()->json(["code" => 1, "message" => "No active subscription."]);

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
            $alert->sendSms = 1;
            $alert->sendEmail = 1;

            $alert->save();

            break;  // no matter how many subscription, we only need 1 entry.
        }

        return response()->json(["code" => 0, "message" => "Successful."]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
