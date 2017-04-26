<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PasswordController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = \Auth::user();
        $profile = \App\Users::select('id')->where('id', $user->id)->get()[0];
        return response()->json($profile);
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
    public function update(Request $request, $userId)
    {
        $user = \Auth::user();
        if ($userId != $user->id)
            return response()->json(["code" => 1, "message" => "Invalid user."]);

        /* Check if old pw is equal to the current. */
        $userHashPw = \App\Users::select('password')->where('id', $user->id)->get()[0];
        if (!\Hash::check($request->oldPassword, $userHashPw->password))
            return response()->json(["code" => 1, "message" => "Invalid previous password."]);

        $Users = \App\Users::find($userId);
        $Users->password = bcrypt($request->newPassword);
        $Users->save();
        return response()->json(["code" => 0, "message" => "Change password successful."]);
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
