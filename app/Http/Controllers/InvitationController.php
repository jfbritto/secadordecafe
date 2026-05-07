<?php

namespace App\Http\Controllers;

use App\Actions\Invitations\SendInvitationAction;
use App\Http\Requests\Invitations\StoreInvitationRequest;
use App\Models\Invitation;
use Illuminate\Http\RedirectResponse;

class InvitationController extends Controller
{
    public function store(StoreInvitationRequest $request, SendInvitationAction $action): RedirectResponse
    {
        $email = $request->validated('email');
        $action->execute(
            inviter: $request->user(),
            email: $email,
            role: $request->validated('role'),
        );

        return redirect()->route('usuarios.index')
            ->with('flash', 'Convite enviado para <strong>' . e($email) . '</strong>.');
    }

    public function destroy(Invitation $convite): RedirectResponse
    {
        $this->authorize('delete', $convite);
        $email = $convite->email;
        $convite->delete();
        return redirect()->route('usuarios.index')
            ->with('flash', 'Convite para <strong>' . e($email) . '</strong> cancelado.');
    }
}
