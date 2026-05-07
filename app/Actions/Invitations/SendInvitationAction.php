<?php

namespace App\Actions\Invitations;

use App\Events\InvitationCreated;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Support\Str;

class SendInvitationAction
{
    public function execute(User $inviter, string $email, string $role): Invitation
    {
        $invitation = Invitation::create([
            'farm_id' => $inviter->farm_id,
            'email' => strtolower($email),
            'role' => $role,
            'token' => Str::random(64),
            'expires_at' => now()->addDays(7),
            'invited_by' => $inviter->id,
        ]);

        InvitationCreated::dispatch($invitation);

        return $invitation;
    }
}
