<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use \App\Mail\ContactUs;
use GuzzleHttp\Client;

class ContactUsController extends Controller
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
        $issue['fName'] = $request->fName;
        $issue['lName'] = $request->lName;
        $issue['email'] = $request->email;
        $issue['phoneNo'] = $request->phoneNo;
        $issue['message'] = $request->message;

        if ($this->verifyRecaptcha($request->{'g-recaptcha-response'})) {
            Mail::to(config('mail.from.address'))->send(new ContactUs($issue));
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
