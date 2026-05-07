<?php

namespace App\Listeners;

use App\Events\InvitationCreated;
use App\Mail\InvitationMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendInvitationEmail implements ShouldQueue
{
    public string $queue = 'emails';

    public function handle(InvitationCreated $event): void
    {
        Mail::to($event->invitation->email)->send(new InvitationMail($event->invitation));
    }
}
