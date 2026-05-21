<?php

namespace App\Policies;

use App\Models\Area;
use App\Models\User;

class AreaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->farm_id !== null;
    }

    public function view(User $user, Area $area): bool
    {
        return $user->farm_id === $area->farm_id;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'operador']);
    }

    public function update(User $user, Area $area): bool
    {
        return $user->farm_id === $area->farm_id
            && $user->hasAnyRole(['admin', 'operador']);
    }

    public function delete(User $user, Area $area): bool
    {
        return $user->farm_id === $area->farm_id
            && $user->hasRole('admin');
    }
}
