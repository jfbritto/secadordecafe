<?php

namespace App\Http\Middleware;

use App\Models\Farm;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureFarmActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user || $user->isRoot()) {
            return $next($request);
        }

        $farm = $user->farm;
        if ($farm && $farm->status === Farm::STATUS_BLOCKED) {
            if ($request->routeIs('farm.blocked') || $request->routeIs('logout')) {
                return $next($request);
            }
            return redirect()->route('farm.blocked');
        }

        return $next($request);
    }
}
