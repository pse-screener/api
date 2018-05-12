<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ContactUs extends Mailable
{
    use Queueable, SerializesModels;

    protected $issue;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($issue)
    {
        $this->issue = $issue;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from('jhunexjun@gmail.com')
                ->subject('Someone\'s having an issue with PSE Screener.')
                ->view('emails.contactus')
                ->with([
                        'fName' => $this->issue['fName'],
                        'lName' => $this->issue['lName'],
                        'email' => $this->issue['email'],
                        'phoneNo' => $this->issue['phoneNo'],
                        'issueMessage' => $this->issue['message']
                    ]);
    }
}
