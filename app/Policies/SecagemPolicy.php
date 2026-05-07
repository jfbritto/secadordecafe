<?php

namespace App\Policies;

use App\Models\Secagem;
use App\Models\User;

class SecagemPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->farm_id !== null;
    }

    public function view(User $user, Secagem $secagem): bool
    {
        return $user->farm_id === $secagem->farm_id;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'operador']);
    }

    public function update(User $user, Secagem $secagem): bool
    {
        return $user->farm_id === $secagem->farm_id
            && $secagem->isRascunho()
            && $user->hasAnyRole(['admin', 'operador']);
    }

    public function delete(User $user, Secagem $secagem): bool
    {
        return $user->farm_id === $secagem->farm_id
            && $secagem->isRascunho()
            && $user->hasRole('admin');
    }

    public function conclude(User $user, Secagem $secagem): bool
    {
        return $user->farm_id === $secagem->farm_id
            && $secagem->isRascunho()
            && $user->hasAnyRole(['admin', 'operador']);
    }
}
