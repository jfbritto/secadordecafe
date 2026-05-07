<?php

namespace App\Policies;

use App\Models\Farm;
use App\Models\User;

class FarmPolicy
{
    public function update(User $user, Farm $farm): bool
    {
        return $user->farm_id === $farm->id && $user->hasRole('admin');
    }
}
