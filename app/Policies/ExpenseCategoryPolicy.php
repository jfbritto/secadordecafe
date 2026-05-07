<?php

namespace App\Policies;

use App\Models\ExpenseCategory;
use App\Models\User;

class ExpenseCategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'financeiro', 'visualizador']);
    }

    public function view(User $user, ExpenseCategory $category): bool
    {
        return $user->farm_id === $category->farm_id;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'financeiro']);
    }

    public function update(User $user, ExpenseCategory $category): bool
    {
        return $user->farm_id === $category->farm_id
            && $user->hasAnyRole(['admin', 'financeiro']);
    }

    public function delete(User $user, ExpenseCategory $category): bool
    {
        return $user->farm_id === $category->farm_id
            && $user->hasRole('admin');
    }
}
