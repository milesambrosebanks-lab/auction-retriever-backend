<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DonationMail extends Mailable {
    use Queueable, SerializesModels;

    public User $donation;

    /**
     * Create a new message instance.
     */
    public function __construct(User $donation) {
        $this->donation = $donation;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope {
        return new Envelope(
            subject: "Thank You for Your Donation!",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content {
        return new Content(
            view: 'mail.donation-mail',
            with: [
                'donation' => $this->donation,
            ],
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
