<?php

namespace App\Mail;


use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class AdminNotificationMail extends Mailable
{
    public function __construct(
        public string $subject,
        public string $message,
        public array  $data = []
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: '[Admin] ' . $this->subject);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.admin-notification');
    }
}
