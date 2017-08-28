<?php

namespace App\Http\Controllers\Auth;

use App\User;
use Validator;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\RegistersUsers;
use Carbon\Carbon;
use GuzzleHttp\Client;

use Illuminate\Http\Request;

use Illuminate\Auth\Passwords\PasswordBroker;
use App\Notifications\Registration;

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
            'g-recaptcha-response' => 'required'
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
        // better to use transaction here.

        $user = User::create([
            'fName' => $data['fName'],
            'lName' => $data['lName'],
            'gender' => $data['gender'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
            'mobileNo' => $data['mobileNo'],
            'activationHash' => $data['activationHash'],
        ]);

        if ($user->id) {
            $date = new \DateTime(date('Y-m-d'));
            $date->add(new \DateInterval('P3M'));

            \App\Subscriptions::create([
                'userId' => $user->id,
                'subscriptionRef' => 'PPP-XLS-ZX-000',
                'paidFromMerchant' => 'None',
                'amountPaid' => 0.00,
                'validUntil' => $date->format('Y-m-d'),
                'subscriptionType' => 'Free',
            ]);
        }

        return $user;
    }

    /* POST these to https://www.google.com/recaptcha/api/siteverify
       https://developers.google.com/recaptcha/docs/verify
    */
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

    public function register(Request $request)
    {
        $prefix = substr($request->mobileNo, 0, 4);
        $id = \App\Telcos::where('mobilePrefix', $prefix)->select('id')->get();
        if (!count($id))
            return response()->json(['code' => 1, 'message' => 'Unknown mobile network.']);        

        $this->validator($request->all())->validate();

        if (!$this->verifyRecaptcha($request->{'g-recaptcha-response'}))
            return response()->json(["code" => 1, "message" => "Registration unsuccessful."]);
        
        $activationHash = str_random(40);
        $request->request->add(['activationHash' => $activationHash]);
        $user = $this->create($request->all());
        $user->notify(new Registration($activationHash));

        return response()->json(["code" => 0, "message" => "Registration successful."]);
    }

    public function emailConfirmation($hash) {
        \App\User::where('activationHash', $hash)->update(['activated' => 1, 'tsActivated' => Carbon::now()]);
        return response()->json(["code" => 0, "message" => "Email confirmation was successful."]);
    }
}
