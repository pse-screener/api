<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use Illuminate\Contracts\Auth\Guard;

class profile extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = \Auth::user();
        $profile = \App\Users::select('id', 'fName', 'lName', 'birthday', 'gender', 'email', 'mobileNo')
            ->where('id', $user->id)
            ->get()[0];
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
    public function update(Request $request, $id)
    {
        $user = \Auth::user();
        if ($id != $user->id)
            return response()->json(["code" => 1, "message" => "Invalid user."]);

        $Users = \App\Users::find($id);
        $Users->fName = $request->fName;
        $Users->lName = $request->lName;
        $birthday = new \DateTime($request->birthday);
        $birthday = $birthday->format('Y-m-d');
        $Users->birthday = $birthday;
        $Users->gender = $request->gender;
        $Users->email = $request->email;
        $Users->mobileNo = $request->mobileNo;

        try {
            $Users->save();
        } catch (Exception $e) {
            
        }
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
