<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RecognitionAvailable extends Mailable
{
    use Queueable, SerializesModels;

    public $teacherName;
    public $announcementTitle;

    public function __construct($teacherName, $announcementTitle)
    {
        $this->teacherName = $teacherName;
        $this->announcementTitle = $announcementTitle;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tu Reconocimiento está disponible - Sistema de Becas TecNM',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.recognition-available',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
