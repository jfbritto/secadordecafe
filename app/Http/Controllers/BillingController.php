<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class BillingController extends Controller
{
    public function show(Request $request): View
    {
        $user = $request->user();
        if (! $user->hasRole('admin') && ! $user->isRoot()) {
            abort(403);
        }

        $farm = $user->farm;
        $subscription = $farm?->subscription;

        return view('billing.show', compact('farm', 'subscription'));
    }
}
