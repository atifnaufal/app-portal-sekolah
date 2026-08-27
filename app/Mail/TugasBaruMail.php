<?php

namespace App\Mail;

use App\Models\Tugas;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TugasBaruMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Tugas $tugas)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tugas Baru: ' . $this->tugas->judul,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.tugas-baru',
        );
    }
}
