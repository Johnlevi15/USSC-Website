<?php

namespace App\Mail;

use App\Models\LostFoundItem;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LostFoundItemStatusUpdated extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public LostFoundItem $item,
        public string $oldStatus,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->statusSubject(),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.lost-found-item-status-updated',
        );
    }

    private function statusSubject(): string
    {
        if ($this->oldStatus === 'lost' && $this->item->status === 'found') {
            return 'USSC Lost & Found Update: Your item may have been found';
        }

        return 'USSC Lost & Found Item Status Update';
    }
}
