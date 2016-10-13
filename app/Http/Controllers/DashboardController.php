<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use Illuminate\Contracts\Auth\Guard;

use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
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

        $user = \Auth::user();
        $subscriptions = \App\Subscriptions::whereRaw("DATE_FORMAT(validUntil, '%Y-%m-%d') >= DATE_FORMAT(NOW(), '%Y-%m-%d')")
                        ->selectRaw('type, amountPaid, DATE_FORMAT(created_at, "%m-%d-%Y") as subscriptionDate, DATE_FORMAT(validUntil, "%m-%d-%Y") as expiryDate')
                        ->where("userId", $user->id)
                        ->take(1) // we only need the first one
                        ->get();

        $dashboard = ['alerts' => $alerts, 'subscriptions' => $subscriptions];

        return response()->json($dashboard);
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

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
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
        //
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
