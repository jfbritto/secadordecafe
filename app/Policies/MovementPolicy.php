<?php

namespace App\Policies;

use App\Models\Movement;
use App\Models\User;

class MovementPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->farm_id !== null;
    }

    /**
     * Cria movimentação manual. Tipo passado em $context['tipo'].
     */
    public function create(User $user, ?string $tipo = null): bool
    {
        if (! $user->hasAnyRole(['admin', 'operador'])) {
            return false;
        }
        if ($tipo === Movement::TIPO_SAIDA) {
            return $user->hasRole('admin');
        }
        return true;
    }
}
