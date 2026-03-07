<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PaymentFailed extends Mailable
{
    use SerializesModels;

    public $name;
    public $retry_url;


    public function __construct($name, $retry_url)
    {
        $this->name = $name;
        $this->retry_url = $retry_url;
    }
    public function build()
    {
        return $this->subject('Payment Failed')
            ->view('mail.payment_failed');
    }
}
