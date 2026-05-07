<?php

namespace App\Providers;

use App\Models\User;
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

        // Eventos: FarmRegistered -> SendWelcomeEmail e InvitationCreated -> SendInvitationEmail
        // são registrados automaticamente pelo auto-discovery do Laravel 11
        // (typehint do parâmetro $event no método handle() do listener).
        // Não registrar manualmente aqui causa duplicação.
    }
}
