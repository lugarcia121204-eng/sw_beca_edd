<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EvaluatorAssigned extends Mailable
{
    use Queueable, SerializesModels;

    public $evaluatorName;
    public $evaluationsCount;
    public $daysLimit;

    /**
     * Create a new message instance.
     */
    public function __construct($evaluatorName, $evaluationsCount, $daysLimit = 5)
    {
        $this->evaluatorName = $evaluatorName;
        $this->evaluationsCount = $evaluationsCount;
        $this->daysLimit = $daysLimit;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nuevas Evaluaciones Asignadas - Sistema de Becas TecNM',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.evaluator-assigned',
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
