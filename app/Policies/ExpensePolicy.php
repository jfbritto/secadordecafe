<?php

namespace App\Policies;

use App\Models\Expense;
use App\Models\User;

class ExpensePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'financeiro', 'visualizador']);
    }

    public function view(User $user, Expense $expense): bool
    {
        return $user->farm_id === $expense->farm_id
            && $user->hasAnyRole(['admin', 'financeiro', 'visualizador']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'financeiro']);
    }

    public function update(User $user, Expense $expense): bool
    {
        return $user->farm_id === $expense->farm_id
            && $user->hasAnyRole(['admin', 'financeiro']);
    }

    public function delete(User $user, Expense $expense): bool
    {
        return $user->farm_id === $expense->farm_id
            && $user->hasRole('admin');
    }
}
