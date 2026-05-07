<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Spatie\Permission\PermissionRegistrar;

class InvitationAcceptController extends Controller
{
    public function show(string $token): View
    {
        $invitation = Invitation::where('token', $token)->firstOrFail();

        if ($invitation->isAccepted()) {
            return view('invitations.already-accepted', compact('invitation'));
        }
        if ($invitation->isExpired()) {
            return view('invitations.expired', compact('invitation'));
        }

        return view('invitations.accept', compact('invitation'));
    }

    public function store(Request $request, string $token): RedirectResponse
    {
        $invitation = Invitation::where('token', $token)->firstOrFail();

        if ($invitation->isAccepted() || $invitation->isExpired()) {
            return redirect()->route('login')->with('error', 'Convite indisponível.');
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);

        $existing = User::where('email', $invitation->email)->first();
        if ($existing) {
            return redirect()->route('login')->with('error', 'Já existe uma conta com este e-mail. Entre e contate o admin da fazenda.');
        }

        $user = DB::transaction(function () use ($invitation, $data) {
            $user = User::create([
                'farm_id' => $invitation->farm_id,
                'name' => $data['name'],
                'email' => $invitation->email,
                'password' => $data['password'],
                'is_root' => false,
                'email_verified_at' => now(),
            ]);

            app(PermissionRegistrar::class)->setPermissionsTeamId($invitation->farm_id);
            $user->assignRole($invitation->role);

            $invitation->update(['accepted_at' => now()]);

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }
}
