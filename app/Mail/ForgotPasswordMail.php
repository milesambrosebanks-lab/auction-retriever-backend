<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class ForgotPasswordMail extends Mailable
{
    public function __construct(
        public string $name,
        public string $verificationUrl
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Please Verify your Email Address');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.reset-password');
    }
}
