<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function view(User $user, User $target): bool
    {
        return $user->farm_id === $target->farm_id && $user->hasRole('admin');
    }

    public function updateRole(User $user, User $target): bool
    {
        return $user->id !== $target->id
            && $user->farm_id === $target->farm_id
            && $user->hasRole('admin');
    }

    public function delete(User $user, User $target): bool
    {
        return $user->id !== $target->id
            && $user->farm_id === $target->farm_id
            && $user->hasRole('admin');
    }
}
