<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WeeklyApplicationsSummary extends Mailable
{
    use Queueable, SerializesModels;

    public array $weeklyData;
    public int $totalApplications;

    /**
     * Create a new message instance.
     */
    public function __construct(array $weeklyData, int $totalApplications)
    {
        $this->weeklyData = $weeklyData;
        $this->totalApplications = $totalApplications;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Resumen Semanal de Solicitudes - Programa de Estímulos',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.weekly-applications-summary',
            with: [
                'weeklyData' => $this->weeklyData,
                'totalApplications' => $this->totalApplications,
            ],
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
