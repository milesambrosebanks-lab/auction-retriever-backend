<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PdfDownloadMail extends Mailable
{
    use SerializesModels;

    public $pdfRequest;
    public $downloadLink;
    public $fileSize;
    public $pdfTitle;

    public function __construct($pdfRequest, $downloadLink, $pdfTitle, $pdfSize)
    {
        $this->pdfRequest = $pdfRequest;
        $this->downloadLink = $downloadLink;
        $this->fileSize = $pdfSize;
        $this->pdfTitle = $pdfTitle;
    }

    public function build()
    {
        return $this->subject('Your PDF Download Link')
            ->view('mail.downloadRequest');
    }
}
