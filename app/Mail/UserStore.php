<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UserStore extends Mailable
{
    use Queueable, SerializesModels;

    public $usuario;
    public $password;

    public function __construct($usuario, $password)
    {
        $this->usuario = $usuario;
        $this->password = $password;
    }

    public function build()
    {
        return $this->view('emails.User-Store')
                    ->subject('');
    }
    
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Bienvenido a nuestro sistema',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.User-Store'
        );
    }

    public function attachments(): array
    {
        return [];
    }
}