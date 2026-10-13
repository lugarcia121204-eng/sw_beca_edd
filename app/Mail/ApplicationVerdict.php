<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ApplicationVerdict extends Mailable
{
    use Queueable, SerializesModels;

    public $teacherName;
    public $status; // 'approved' or 'rejected'
    public $announcementTitle;

    /**
     * Create a new message instance.
     */
    public function __construct($teacherName, $status, $announcementTitle)
    {
        $this->teacherName = $teacherName;
        $this->status = $status;
        $this->announcementTitle = $announcementTitle;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->status === 'approved'
            ? 'Solicitud Aprobada - Sistema de Becas TecNM'
            : 'Solicitud No Aceptada - Sistema de Becas TecNM';

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.application-verdict',
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
