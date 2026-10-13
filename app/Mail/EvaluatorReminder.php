<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EvaluatorReminder extends Mailable
{
    use Queueable, SerializesModels;

    public string $evaluatorName;
    public int $pendingCount;
    public int $daysLeft;

    public function __construct(string $evaluatorName, int $pendingCount, int $daysLeft)
    {
        $this->evaluatorName = $evaluatorName;
        $this->pendingCount  = $pendingCount;
        $this->daysLeft      = $daysLeft;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '⏰ Recordatorio: Evaluaciones pendientes - Sistema de Becas TecNM',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.evaluator-reminder',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
