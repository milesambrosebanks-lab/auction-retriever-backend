<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $title;
    public $order;

    /**
     * Create a new message instance.
     */
    public function __construct(string $title, $order_id)
    {
        $this->title = $title;
        $this->order = Order::with(['user', 'items'])->findOrFail($order_id);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->title,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mail.invoice-mail',
            with: [
                'order' => $this->order,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        $pdf = Pdf::loadView('pdf.invoice', ['order' => $this->order]);

        return [
            Attachment::fromData(fn () => $pdf->output(), 'invoice.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
