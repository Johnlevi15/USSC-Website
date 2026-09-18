<?php

namespace App\Mail;

use App\Models\DocumentRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DocumentRequestStatusUpdated extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public DocumentRequest $documentRequest)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'USSC Document Request Status Update',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.document-request-status-updated',
        );
    }
}
