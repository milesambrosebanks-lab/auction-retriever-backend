<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordMail extends Mailable {
    use Queueable, SerializesModels;
    public int $password;
    public User $user;
    /**
     * Create a new message instance.
     */
    public function __construct(User $user, int $password) {
        $this->user = $user;
        $this->password = $password;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope {
        return new Envelope(
            subject: 'Your Password',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content {
        return new Content(
            view: 'mail.passwordmail',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array {
        return [];
    }
}
