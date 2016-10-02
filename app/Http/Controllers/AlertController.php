<?php

namespace App\Http\Controllers;

use Validator;

use Illuminate\Http\Request;

// use App\Http\Requests;

// use App\User;

use Illuminate\Support\Facades\Auth;
// use Illuminate\Auth\GuardHelpers;
// use Illuminate\Foundation\Auth\RegistersUsers;

class AlertController extends Controller
{
    // use GuardHelpers;
    // use RegistersUsers;
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

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'companyId' => 'bail|required|max:3',
            'priceCondition' => 'required|in:MA,MB',
            'price' => 'required|numeric',
        ]);

        // this may not be the correct way. Probably we'll be using the scrf_token so that 
        // $user = Auth::user() will work. Let's see.
        $headerValue = $request->header('Authorization');
        // sample output: "Bearer 23fa89991653677fa4708393b0f767b2ee319d48ff35fb802e3ba09311ff0c5b74a47695e9b2c615"

        preg_match("/Bearer\s+(.*)/", $headerValue, $match);
        $headerValue = $match[1];
        
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
