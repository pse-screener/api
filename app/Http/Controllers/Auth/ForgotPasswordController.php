<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;

use Illuminate\Http\Request;

use Illuminate\Auth\Passwords\PasswordBroker;

use App\Notifications\My_PasswordReset;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

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
     * Send a reset link to the given user. (An overridden method).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return json response
     */

    public function sendResetLinkEmail(Request $request)
    {
        $this->validate($request, ['email' => 'required|email']);

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        /*
        $response = $this->broker()->sendResetLink(
            $request->only('email'), $this->resetNotifier()
        );

        if ($response === Password::RESET_LINK_SENT) {
            return response()->json(["code" => 0, "message" => "Successfully sent your reset password link. Please check your email address."]);
        }

        return response()->json(["code" => 1, "message" => trans($response)]);*/

        // another way (unsuccessful).

        /*$response = Password::sendResetLink($request->only('email'), function (Message $message) {
            file_put_contents('/tmp/message.txt', print_r($message, true));
            $message->subject(Config::get('auth.recovery_email_subject'));
        });

        if ($response === Password::RESET_LINK_SENT) {
            return response()->json(["code" => 0, "message" => "Successfully sent your reset password link. Please check your email address."]);
        }

        return response()->json(["code" => 1, "message" => trans($response)]);*/

        // another way. Success!    
        // https://laracasts.com/discuss/channels/laravel/get-a-password-reset-token. Thanks to @robgeorgeuk.
        $user = \App\User::where('email', $request->only('email'))->first();
        $token = app('auth.password.broker')->createToken($user);
        $user->notify(new My_PasswordReset($token));

    }
}
