<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PaymentSuccess extends Mailable
{
    use SerializesModels;

    public $name;
    public $transaction_id;
    public $amount;
    public $date;

    public function __construct($name, $transaction_id, $amount, $date)
    {
        $this->name = $name;
        $this->transaction_id = $transaction_id;
        $this->amount = $amount;
        $this->date = $date;
    }

    public function build()
    {
        return $this->subject('Payment success')
            ->view('mail.payment_success');
    }
}
