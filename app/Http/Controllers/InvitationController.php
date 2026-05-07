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
        $action->execute(
            inviter: $request->user(),
            email: $request->validated('email'),
            role: $request->validated('role'),
        );

        return redirect()->route('usuarios.index')->with('flash', 'Convite enviado.');
    }

    public function destroy(Invitation $convite): RedirectResponse
    {
        $this->authorize('delete', $convite);
        $convite->delete();
        return redirect()->route('usuarios.index')->with('flash', 'Convite cancelado.');
    }
}
