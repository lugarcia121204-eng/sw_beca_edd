<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AnnouncementClosed extends Mailable
{
    use Queueable, SerializesModels;

    public $userName;
    public $announcementTitle;
    public $resultsEnd;

    /**
     * Create a new message instance.
     */
    public function __construct($userName, $announcementTitle, $resultsEnd = null)
    {
        $this->userName = $userName;
        $this->announcementTitle = $announcementTitle;
        $this->resultsEnd = $resultsEnd;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Convocatoria Finalizada - Sistema de Becas TecNM',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.announcement-closed',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
