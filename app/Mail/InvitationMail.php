<?php

namespace App\Mail;

use App\Models\Invitation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvitationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Invitation $invitation) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Convite para acessar a fazenda');
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.invitation',
            with: [
                'farmName' => $this->invitation->farm?->nome,
                'role' => $this->invitation->role,
                'acceptUrl' => route('convite.show', ['token' => $this->invitation->token]),
                'expiresAt' => $this->invitation->expires_at,
            ],
        );
    }
}
