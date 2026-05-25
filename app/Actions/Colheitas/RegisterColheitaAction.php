<?php

namespace App\Actions\Colheitas;

use App\Actions\Movements\RegisterMovementAction;
use App\Models\Area;
use App\Models\Movement;
use App\Models\User;

/**
 * Registra colheita de café côco numa Área. Materializa o estoque de côco
 * que ficará disponível pra secagem do próprio produtor.
 *
 * Wrapper fino sobre RegisterMovementAction — existe pra dar nome ao
 * domínio e poder evoluir (validações específicas, eventos, etc) sem mexer
 * no controlador.
 */
class RegisterColheitaAction
{
    public function __construct(private RegisterMovementAction $registerMovement)
    {
    }

    public function execute(
        Area $area,
        User $user,
        float $quantidadeKg,
        ?string $observacao = null,
        ?\DateTimeInterface $occurredAt = null,
    ): Movement {
        return $this->registerMovement->execute(
            owner: $area,
            user: $user,
            tipo: Movement::TIPO_COLHEITA,
            produto: Movement::PRODUTO_COCO,
            quantidade: $quantidadeKg,
            observacao: $observacao,
            occurredAt: $occurredAt,
        );
    }
}
