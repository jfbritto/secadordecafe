<?php

namespace App\Actions\Secagens;

use App\Actions\Movements\RegisterMovementAction;
use App\Exceptions\DomainException;
use App\Models\Movement;
use App\Models\Secagem;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Reabre uma secagem concluída pra correção:
 * - Estorna o débito de cada item (cria Movement de ajuste positivo com source=Secagem)
 * - Volta status pra "rascunho" e zera concluida_at
 *
 * Auditoria fica completa: o extrato do cliente mostra Secagem(-) → Estorno(+) → Secagem(-) novo.
 * Tudo em transação — falha em qualquer estorno cancela a operação inteira.
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

        $secagem->loadMissing('items.customer');

        return DB::transaction(function () use ($secagem, $user) {
            $occurredAt = now();

            foreach ($secagem->items as $item) {
                // Estorno: ajuste positivo no saldo do cliente, com a Secagem como source
                $this->registerMovement->execute(
                    customer: $item->customer,
                    user: $user,
                    tipo: Movement::TIPO_AJUSTE,
                    quantidade: (float) $item->quantidade_recebida_kg,
                    observacao: "Estorno secagem #{$secagem->numero}",
                    direcao: '+',
                    occurredAt: $occurredAt,
                    source: $secagem,
                );
            }

            $secagem->status = Secagem::STATUS_RASCUNHO;
            $secagem->concluida_at = null;
            $secagem->save();

            return $secagem->fresh('items.customer');
        });
    }
}
