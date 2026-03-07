<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminCustomMail extends Mailable
{
    use SerializesModels;

    public $content;

    public function __construct($subject, $content)
    {
        $this->subject($subject);
        $this->content = $content;
    }

    public function build()
    {
        return $this->view('mail.message')->with(['content' => $this->content]);
    }
}