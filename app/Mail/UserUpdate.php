<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UserUpdate extends Mailable
{
    use Queueable, SerializesModels;

    public $usuarios;

    public function __construct($usuarios)
    {
        $this->usuarios = $usuarios;
    }

    public function build()
    {
        return $this->view('emails.User-Update')
                    ->subject('');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Cambios en tu perfil',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.User-Update'
        );
    }

    public function attachments(): array
    {
        return [];
    }
}