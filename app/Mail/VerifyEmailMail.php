<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class VerifyEmailMail extends Mailable
{
    public function __construct(
        public string $name,
        public string $verificationUrl
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Verify your email — MilesBanks');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.verify-email');
    }
}
