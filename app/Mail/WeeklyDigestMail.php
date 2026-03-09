<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class WeeklyDigestMail extends Mailable
{
    public $listings;

    public function __construct($listings)
    {
        $this->listings = $listings;
    }

    public function build()
    {
        return $this->subject('Weekly Property Digest')
            ->view('mail.weekly-digest');
    }
}
