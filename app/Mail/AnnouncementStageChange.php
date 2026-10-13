<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AnnouncementStageChange extends Mailable
{
    use Queueable, SerializesModels;

    public $userName;
    public $announcementTitle;
    public $newStage;
    public $stageDate;

    /**
     * Create a new message instance.
     */
    public function __construct($userName, $announcementTitle, $newStage, $stageDate = null)
    {
        $this->userName = $userName;
        $this->announcementTitle = $announcementTitle;
        $this->newStage = $newStage;
        $this->stageDate = $stageDate;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Cambio de Etapa en Convocatoria - Sistema de Becas TecNM',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.announcement-stage-change',
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
