<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class OtpMail extends Mailable
{
    public function __construct(
        public string  $otp = '',
        public string  $type = 'registration',
        public ?string $verificationUrl = null // ← optional
    ) {}

    public function envelope(): Envelope
    {
        $subject = match(true) {
            !is_null($this->verificationUrl) => 'Verify your email — MilesBanks',
            $this->type === 'password_reset' => 'Your password reset OTP',
            default                          => 'Verify your email — MilesBanks',
        };

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.otp');
    }
}
