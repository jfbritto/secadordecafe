<?php

namespace App\Actions\Secagens;

use App\Exceptions\DomainException;
use App\Models\Customer;
use App\Models\Movement;
use App\Models\Secagem;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ConcludeSecagemAction
{
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

            // Validar saldos
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

            // Aplicar débitos + criar movements
            foreach ($secagem->items as $item) {
                $customer = $locked[$item->customer_id];
                $debit = (float) $item->quantidade_recebida_kg;

                Movement::create([
                    'farm_id' => $secagem->farm_id,
                    'customer_id' => $customer->id,
                    'user_id' => $user->id,
                    'tipo' => Movement::TIPO_SECAGEM,
                    'quantidade_kg' => -$debit,
                    'observacao' => "Secagem #{$secagem->numero}",
                    'source_type' => $item->getMorphClass(),
                    'source_id' => $item->id,
                    'occurred_at' => $secagem->data->setTime(now()->hour, now()->minute, now()->second),
                ]);

                $customer->saldo_cafe_kg = (float) $customer->saldo_cafe_kg - $debit;
                $customer->save();
            }

            $secagem->status = Secagem::STATUS_CONCLUIDA;
            $secagem->concluida_at = now();
            $secagem->save();

            return $secagem->fresh('items.customer');
        });
    }
}
