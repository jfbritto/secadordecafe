<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\RegisterFarmAction;
use App\DTOs\RegisterFarmData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterFarmRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function show(): View
    {
        return view('auth.register');
    }

    public function store(RegisterFarmRequest $request, RegisterFarmAction $action): RedirectResponse
    {
        $user = $action->execute(RegisterFarmData::fromArray($request->validated()));

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }
}
