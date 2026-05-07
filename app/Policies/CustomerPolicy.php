<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\User;

class CustomerPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->farm_id !== null;
    }

    public function view(User $user, Customer $customer): bool
    {
        return $user->farm_id === $customer->farm_id;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'operador']);
    }

    public function update(User $user, Customer $customer): bool
    {
        return $user->farm_id === $customer->farm_id
            && $user->hasAnyRole(['admin', 'operador']);
    }

    public function delete(User $user, Customer $customer): bool
    {
        return $user->farm_id === $customer->farm_id
            && $user->hasRole('admin');
    }
}
