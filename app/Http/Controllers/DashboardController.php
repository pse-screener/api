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
        $lastClosedDate = DB::table('materialize_per_company_daily')->max('asOf');

        $user = \Auth::user();
        $alerts = DB::table('alerts')
                    ->join('companies', 'alerts.companyId', '=', 'companies.id')
                    ->join('subscriptions', 'alerts.subscriptionId', '=', 'subscriptions.id')
                    ->join('materialize_per_company_daily as mpcd', 'companies.id', '=', 'mpcd.companyId')
                    ->select('alerts.id', 'symbol', 'companyName', 'priceCondition', 'alerts.price', 'sentToSms', 'mpcd.price as lastClosedPrice')
                    ->where('subscriptions.userId', '=', $user->id)
                    ->where('mpcd.asOf', $lastClosedDate)
                    ->orderBy('companyName', 'ASC')
                    ->get();

        $lastClosedDate = \DateTime::createFromFormat('Y-m-d', $lastClosedDate);
        $lastClosedDate = $lastClosedDate->format("D M d, Y");

        $dashboard = ['alerts' => $alerts, 'asOf' => $lastClosedDate];

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
