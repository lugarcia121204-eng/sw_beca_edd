<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UserDelete extends Mailable
{
    use Queueable, SerializesModels;
    public $name;
    public function __construct($name)
    {
        $this->name = $name;
    }

    public function build()
    {
        return $this->view('emails.User-Delete')
                    ->subject('');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Importante: Eliminación de tu Cuenta del Sistema del TecNM',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.User-Delete'
        );
    }

    public function attachments(): array
    {
        return [];
    }
}