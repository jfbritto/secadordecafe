<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\HttpFoundation\Response;

class SetTenantContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user && $user->farm_id) {
            app()->instance('current_farm', $user->farm);
            app(PermissionRegistrar::class)->setPermissionsTeamId($user->farm_id);
            Log::shareContext(['farm_id' => $user->farm_id, 'user_id' => $user->id]);
        }

        return $next($request);
    }
}
