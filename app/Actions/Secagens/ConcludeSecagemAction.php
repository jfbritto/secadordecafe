<?php

namespace App\Actions\Secagens;

use App\Actions\Movements\RegisterMovementAction;
use App\Exceptions\DomainException;
use App\Models\Customer;
use App\Models\Movement;
use App\Models\Secagem;
use App\Models\User;
use Illuminate\Support\Facades\DB;

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

        $secagem->loadMissing('items.customer');
        if ($secagem->items->isEmpty()) {
            throw new DomainException('Adicione ao menos um item antes de concluir.');
        }

        return DB::transaction(function () use ($secagem, $user) {
            $customerIds = $secagem->items->pluck('customer_id')->unique()->values();
            $locked = Customer::query()
                ->whereIn('id', $customerIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            // Validar saldos antes de aplicar (defesa em profundidade — o item-add já valida)
            foreach ($secagem->items as $item) {
                $customer = $locked[$item->customer_id] ?? null;
                if (! $customer) {
                    throw new DomainException('Cliente não encontrado.');
                }
                if ($customer->farm_id !== $secagem->farm_id) {
                    throw new DomainException('Cliente fora da fazenda.');
                }
                if ((float) $customer->saldo_cafe_kg < (float) $item->quantidade_recebida_kg) {
                    throw new DomainException(
                        "Saldo insuficiente para {$customer->nome} (precisa "
                        . number_format((float) $item->quantidade_recebida_kg, 3, ',', '.')
                        . ' kg, tem '
                        . number_format((float) $customer->saldo_cafe_kg, 3, ',', '.')
                        . ' kg).'
                    );
                }
            }

            // Cada item vira um Movement (tipo=secagem) com source = Secagem.
            // O RegisterMovementAction cuida do lock/transação/saldo.
            $occurredAt = $secagem->data->setTime(now()->hour, now()->minute, now()->second);
            foreach ($secagem->items as $item) {
                $this->registerMovement->execute(
                    customer: $locked[$item->customer_id],
                    user: $user,
                    tipo: Movement::TIPO_SECAGEM,
                    quantidade: (float) $item->quantidade_recebida_kg,
                    observacao: "Secagem #{$secagem->numero}",
                    occurredAt: $occurredAt,
                    source: $secagem,
                );
            }

            $secagem->status = Secagem::STATUS_CONCLUIDA;
            $secagem->concluida_at = now();
            $secagem->save();

            return $secagem->fresh('items.customer');
        });
    }
}
