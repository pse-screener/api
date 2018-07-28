<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\SmsMessage;
use Illuminate\Support\Facades\DB;

class SmsMessagesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $records = \App\SmsMessage::whereIn('status', ['draft', 'outbox'])->get();
        $smsMessages = [];
        foreach ($records as $record)
            $smsMessages[] = ['id'=> $record->id, 'alertId' => $record->alertId, 'recipient' => $record->recipient, 'message' => $record->message, 'status' => $record->status, 'created_at' => $record->created_at->toDateTimeString()];

        foreach ($smsMessages as $smsMessage) {
            DB::beginTransaction();
                if ($smsMessage['alertId'])
                    DB::table('alerts')->where('id', $smsMessage['alertId'])->update(['sentToSms' => 1]);

                DB::table('smsMessages')->where('id', $smsMessage['id'])->update(['status' => 'sent']);
            DB::commit();
        }

        return response()->json($smsMessages);
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
