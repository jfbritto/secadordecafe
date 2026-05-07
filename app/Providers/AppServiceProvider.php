<?php

namespace App\Providers;

use App\Events\FarmRegistered;
use App\Listeners\SendWelcomeEmail;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::before(function (User $user) {
            return $user->isRoot() ? true : null;
        });

        Event::listen(FarmRegistered::class, SendWelcomeEmail::class);
    }
}
