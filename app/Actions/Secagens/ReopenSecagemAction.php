<?php

namespace App\Actions\Secagens;

use App\Actions\Movements\RegisterMovementAction;
use App\Exceptions\DomainException;
use App\Models\Movement;
use App\Models\Secagem;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Reabre uma secagem concluída pra correção.
 *
 * Varre TODOS os movements gerados por essa secagem (source=Secagem) e gera um
 * ajuste com sinal contrário pra cada um — funciona qualquer owner (Customer,
 * Area, Farm) e qualquer produto (côco, seco).
 *
 * Auditoria preservada: extrato mostra movement original → ajuste de estorno
 * → quando reconcluir, novos movements. Tudo encadeado por source=Secagem.
 */
class ReopenSecagemAction
{
    public function __construct(private RegisterMovementAction $registerMovement)
    {
    }

    public function execute(Secagem $secagem, User $user): Secagem
    {
        if (! $secagem->isConcluida()) {
            throw new DomainException('Só dá pra reabrir uma secagem concluída.');
        }

        return DB::transaction(function () use ($secagem, $user) {
            $occurredAt = now();

            $movements = Movement::query()
                ->where('source_type', $secagem->getMorphClass())
                ->where('source_id', $secagem->id)
                ->whereNot('tipo', Movement::TIPO_AJUSTE) // não estornar estornos anteriores
                ->with('owner')
                ->get();

            foreach ($movements as $m) {
                $owner = $m->owner;
                if (! $owner) {
                    throw new DomainException("Owner do movement #{$m->id} desapareceu — estorno impossível.");
                }

                // Movement original tem sinal embutido em quantidade_kg (positivo ou negativo).
                // Pra estornar, geramos um ajuste com sinal contrário.
                $qty = (float) $m->quantidade_kg;
                $direcao = $qty > 0 ? '-' : '+';
                $modulo = abs($qty);

                $this->registerMovement->execute(
                    owner: $owner,
                    user: $user,
                    tipo: Movement::TIPO_AJUSTE,
                    produto: $m->produto,
                    quantidade: $modulo,
                    observacao: "Estorno secagem #{$secagem->numero}",
                    direcao: $direcao,
                    occurredAt: $occurredAt,
                    source: $secagem,
                    areaId: $m->area_id,
                );
            }

            // Zera campos calculados nos items (eles ficam pendentes de nova saída)
            foreach ($secagem->items as $item) {
                $item->comissao_kg = null;
                $item->saldo_liquido_kg = null;
                $item->save();
            }

            $secagem->status = Secagem::STATUS_RASCUNHO;
            $secagem->concluida_at = null;
            $secagem->save();

            return $secagem->fresh('items.origin');
        });
    }
}
