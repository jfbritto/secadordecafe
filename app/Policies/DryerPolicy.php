<?php

namespace App\Policies;

use App\Models\Dryer;
use App\Models\User;

class DryerPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->farm_id !== null;
    }

    public function view(User $user, Dryer $dryer): bool
    {
        return $user->farm_id === $dryer->farm_id;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'operador']);
    }

    public function update(User $user, Dryer $dryer): bool
    {
        return $user->farm_id === $dryer->farm_id
            && $user->hasAnyRole(['admin', 'operador']);
    }

    public function delete(User $user, Dryer $dryer): bool
    {
        return $user->farm_id === $dryer->farm_id
            && $user->hasRole('admin');
    }
}
