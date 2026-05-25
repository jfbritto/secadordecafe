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
     * Cria movimentação manual. Tipo passado como segundo argumento.
     * Tipos automáticos (secagem, producao, comissao) NÃO devem ser criados via UI.
     */
    public function create(User $user, ?string $tipo = null): bool
    {
        if (! $user->hasAnyRole(['admin', 'operador'])) {
            return false;
        }
        if (in_array($tipo, [Movement::TIPO_SECAGEM, Movement::TIPO_PRODUCAO, Movement::TIPO_COMISSAO], true)) {
            return false;
        }
        if ($tipo === Movement::TIPO_SAIDA) {
            return $user->hasRole('admin');
        }
        return true;
    }
}
