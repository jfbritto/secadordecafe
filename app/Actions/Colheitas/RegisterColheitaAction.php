<?php

namespace App\Actions\Colheitas;

use App\Actions\Movements\RegisterMovementAction;
use App\Models\Area;
use App\Models\Farm;
use App\Models\Movement;
use App\Models\User;

/**
 * Registra colheita de café côco numa Área.
 *
 * Owner do movement é a Farm (estoque do produtor); a Area entra como
 * `area_id` pra rastrear o histórico de produção por talhão.
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
        $farm = Farm::query()->whereKey($area->farm_id)->firstOrFail();

        return $this->registerMovement->execute(
            owner: $farm,
            user: $user,
            tipo: Movement::TIPO_COLHEITA,
            produto: Movement::PRODUTO_COCO,
            quantidade: $quantidadeKg,
            observacao: $observacao ?? "Colheita {$area->nome}",
            occurredAt: $occurredAt,
            areaId: $area->id,
        );
    }
}
