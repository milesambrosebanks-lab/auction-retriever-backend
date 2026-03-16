<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class AccountStatusMail extends Mailable
{
    public function __construct(
        public string $name,
        public string $status // active | inactive
    ) {}

    public function envelope(): Envelope
    {
        $subject = $this->status === 'active'
            ? 'Your account has been activated'
            : 'Your account has been deactivated';

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.account-status');
    }
}
