<?php

namespace App\Listeners;

use App\Events\FarmRegistered;
use App\Mail\WelcomeFarmMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendWelcomeEmail implements ShouldQueue
{
    public string $queue = 'emails';

    public function handle(FarmRegistered $event): void
    {
        Mail::to($event->user->email)->send(new WelcomeFarmMail($event->user));
    }
}
