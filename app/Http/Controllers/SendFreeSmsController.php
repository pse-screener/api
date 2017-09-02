<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Client;

class SendFreeSmsController extends Controller
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

    private function verifyRecaptcha($recaptchaString)
    {
        $secret = '6LfmBQcUAAAAAFlhY9BUcX9ugyO6uopV_6GtziKU';

        $client = new Client();    
        $response = $client->post('https://www.google.com/recaptcha/api/siteverify',
            ['form_params'=>
                [
                    'secret'=> $secret,
                    'response'=> $recaptchaString
                 ]
            ]
        );
    
        $body = json_decode((string)$response->getBody());
        return $body->success;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (strlen($request->phoneNo) != 11)
            return response()->json(['code'=> 0, 'message'=> 'Invalid phone number. Please enter 11 digit number.']);

        if (strlen($request->message) < 1)
            return response()->json(['code'=> 0, 'message'=> 'Message is empty.']);

        $mobilePrefix = substr($request->phoneNo, 0, 4);
        $telco = DB::table('telcos')->select('network')->where('mobilePrefix', $mobilePrefix)->first();

        if (!$telco)
            return response()->json(['code'=> 0, 'message'=> 'Unknown mobile network.']);

        if ($this->verifyRecaptcha($request->{'g-recaptcha-response'})) {
            DB::table('smsMessages')->insert(['recipient' => $request->phoneNo, 'message'=> $request->message]);
            return response()->json(['code'=> 0, 'message'=> 'Message successfully sent!']);
        } else {
            return response()->json(['code'=> 1, 'message'=> 'Invalid captcha.']);
        }
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
