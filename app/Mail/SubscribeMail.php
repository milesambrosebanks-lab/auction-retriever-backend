<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SubscribeMail extends Mailable
{
    use SerializesModels;

    public $subscriber;

    public function __construct($subscriber)
    {
        $this->subscriber = $subscriber;
    }

    public function build()
    {
        return $this->subject('Welcome & Subscription Confirmation')
                    ->view('mail.subcribtions');
    }
}
