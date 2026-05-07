<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeFarmMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public User $user) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Bem-vindo ao secadordecafe',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.welcome-farm',
            with: [
                'userName' => $this->user->name,
                'farmName' => $this->user->farm?->nome,
                'dashboardUrl' => route('dashboard'),
            ],
        );
    }
}
