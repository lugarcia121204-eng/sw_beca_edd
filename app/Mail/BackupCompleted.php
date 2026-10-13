<?php

namespace App\Mail;

use App\Models\Backup;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BackupCompleted extends Mailable
{
    use Queueable, SerializesModels;

    public Backup $backup;
    public string $formattedSize;

    public function __construct(Backup $backup)
    {
        $this->backup = $backup;

        $bytes = $backup->file_size ?? 0;
        if ($bytes >= 1048576) {
            $this->formattedSize = number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            $this->formattedSize = number_format($bytes / 1024, 2) . ' KB';
        } else {
            $this->formattedSize = $bytes . ' B';
        }
    }

    public function envelope(): Envelope
    {
        $typeLabel = $this->backup->type === 'scheduled' ? 'Automático' : 'Manual';
        return new Envelope(
            subject: "✅ Respaldo {$typeLabel} Completado — {$this->backup->name}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.backup-completed',
            with: [
                'backup'        => $this->backup,
                'formattedSize' => $this->formattedSize,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
