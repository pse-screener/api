<?php

namespace App\Http\Controllers\Auth;

use App\User;
use Validator;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\RegistersUsers;

use Illuminate\Http\Request;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after login / registration.
     *
     * @var string
     */
    protected $redirectTo = '/home'; //we don't redirect for api-based

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'fName' => 'required|max:50',
            'lName' => 'required|max:50',
            'gender' => 'in:M,F',
            'email' => 'required|email|max:100|unique:users',
            'password' => 'required|min:6|confirmed',
            'mobileNo' => 'required|min:11|max:11|unique:users',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
    protected function create(array $data)
    {
        return User::create([
            'fName' => $data['fName'],
            'lName' => $data['lName'],
            'gender' => $data['gender'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
            'mobileNo' => $data['mobileNo'],
        ]);
    }

    public function register(Request $request)
    {
        $prefix = substr($request->mobileNo, 0, 4);
        $id = \App\Telcos::where('mobilePrefix', $prefix)->select('id')->get();
        if (!count($id))
            return response()->json(['code' => 1, 'message' => 'Unknown mobile network.']);        

        $this->validator($request->all())->validate();

        // $this->guard()->login($this->create($request->all()));
        // $this->create($request->all());

        return response()->json(["code" => 0, "message" => "Registration successful."]);
    }
}
