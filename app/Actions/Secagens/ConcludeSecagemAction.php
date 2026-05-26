<?php

namespace App\Actions\Secagens;

use App\Actions\Movements\RegisterMovementAction;
use App\Exceptions\DomainException;
use App\Models\Customer;
use App\Models\Farm;
use App\Models\Movement;
use App\Models\Secagem;
use App\Models\SecagemItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Conclui uma secagem em rascunho. Pra cada item gera:
 *   1. -côco do owner (consome a matéria-prima): tipo=secagem
 *   2. +seco do owner (líquido após comissão):   tipo=producao
 *   3. +seco da Farm (comissão, só se origem=Customer e comissão > 0): tipo=comissao
 *
 * Itens de Area não geram comissão (café próprio fica integralmente com a Farm via Area).
 *
 * Pré-requisitos:
 *  - Secagem em rascunho
 *  - Todos os items com quantidade_seca_kg preenchida (saída registrada)
 *  - Saldo de côco suficiente em cada owner
 */
class ConcludeSecagemAction
{
    public function __construct(private RegisterMovementAction $registerMovement)
    {
    }

    public function execute(Secagem $secagem, User $user): Secagem
    {
        if ($secagem->isConcluida()) {
            throw new DomainException('Secagem já está concluída.');
        }

        $secagem->loadMissing('items.origin');
        if ($secagem->items->isEmpty()) {
            throw new DomainException('Adicione ao menos um item antes de concluir.');
        }

        foreach ($secagem->items as $item) {
            if (! $item->hasSaida()) {
                throw new DomainException("Registre a saída do item de {$item->originLabel()} antes de concluir.");
            }
        }

        return DB::transaction(function () use ($secagem, $user) {
            $occurredAt = $secagem->data->setTime(now()->hour, now()->minute, now()->second);
            $farm = Farm::query()->whereKey($secagem->farm_id)->first();

            foreach ($secagem->items as $item) {
                $origin = $item->origin;
                if (! $origin) {
                    throw new DomainException('Item sem origem definida.');
                }

                // Pra item de Area, o owner do movement é a Farm (estoque próprio
                // do produtor é unificado); a Area entra só como rótulo via area_id.
                [$owner, $areaId] = $item->isArea() ? [$farm, $origin->id] : [$origin, null];

                // 1. Debita o côco do owner
                $this->registerMovement->execute(
                    owner: $owner,
                    user: $user,
                    tipo: Movement::TIPO_SECAGEM,
                    produto: Movement::PRODUTO_COCO,
                    quantidade: (float) $item->quantidade_recebida_kg,
                    observacao: "Secagem #{$secagem->numero}",
                    occurredAt: $occurredAt,
                    source: $secagem,
                    areaId: $areaId,
                );

                // Calcula comissão (sempre 0 se origem=Area)
                $seca = (float) $item->quantidade_seca_kg;
                $percentual = $item->isCustomer() ? (float) ($item->comissao_percentual ?? 0) : 0;
                $comissaoKg = SecagemItem::calcularComissao($seca, $percentual);
                $liquido = SecagemItem::calcularSaldoLiquido($seca, $comissaoKg);

                // 2. Credita seco no owner (líquido)
                if ($liquido > 0) {
                    $this->registerMovement->execute(
                        owner: $owner,
                        user: $user,
                        tipo: Movement::TIPO_PRODUCAO,
                        produto: Movement::PRODUTO_SECO,
                        quantidade: $liquido,
                        observacao: "Produção secagem #{$secagem->numero}",
                        occurredAt: $occurredAt,
                        source: $secagem,
                        areaId: $areaId,
                    );
                }

                // 3. Comissão pra Farm (só se houver e origem=Customer)
                if ($comissaoKg > 0 && $item->isCustomer()) {
                    $this->registerMovement->execute(
                        owner: $farm,
                        user: $user,
                        tipo: Movement::TIPO_COMISSAO,
                        produto: Movement::PRODUTO_SECO,
                        quantidade: $comissaoKg,
                        observacao: "Comissão secagem #{$secagem->numero} ({$origin->nome})",
                        occurredAt: $occurredAt,
                        source: $secagem,
                    );
                }

                // Persiste os campos calculados no item
                $item->comissao_kg = $comissaoKg;
                $item->saldo_liquido_kg = $liquido;
                $item->save();
            }

            $secagem->status = Secagem::STATUS_CONCLUIDA;
            $secagem->concluida_at = now();
            $secagem->save();

            return $secagem->fresh('items.origin');
        });
    }
}
