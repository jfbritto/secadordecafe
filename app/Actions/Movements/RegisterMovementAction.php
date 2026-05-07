<?php

namespace App\Actions\Movements;

use App\Exceptions\DomainException;
use App\Models\Customer;
use App\Models\Movement;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RegisterMovementAction
{
    /**
     * Cria movimentação manual (entrada / ajuste / saida) e atualiza saldo.
     *
     * @param string $tipo  one of entrada|saida|ajuste
     * @param string $direcao  '+' ou '-' (apenas para ajuste; ignorado para entrada/saida)
     * @param float  $quantidade  sempre positivo (módulo)
     */
    public function execute(
        Customer $customer,
        User $user,
        string $tipo,
        float $quantidade,
        ?string $observacao = null,
        ?string $direcao = null,
        ?\DateTimeInterface $occurredAt = null,
    ): Movement {
        if ($quantidade <= 0) {
            throw new DomainException('Quantidade deve ser maior que zero.');
        }

        $signed = match ($tipo) {
            Movement::TIPO_ENTRADA => +$quantidade,
            Movement::TIPO_SAIDA => -$quantidade,
            Movement::TIPO_AJUSTE => $direcao === '-' ? -$quantidade : +$quantidade,
            default => throw new DomainException("Tipo inválido: {$tipo}"),
        };

        return DB::transaction(function () use ($customer, $user, $tipo, $signed, $observacao, $occurredAt) {
            $locked = Customer::query()->whereKey($customer->id)->lockForUpdate()->first();
            if (! $locked) {
                throw new DomainException('Cliente não encontrado.');
            }

            $newSaldo = (float) $locked->saldo_cafe_kg + $signed;
            if ($newSaldo < 0) {
                throw new DomainException('Saldo insuficiente para esta operação.');
            }

            $movement = Movement::create([
                'farm_id' => $locked->farm_id,
                'customer_id' => $locked->id,
                'user_id' => $user->id,
                'tipo' => $tipo,
                'quantidade_kg' => $signed,
                'observacao' => $observacao,
                'occurred_at' => $occurredAt ?? now(),
            ]);

            $locked->saldo_cafe_kg = $newSaldo;
            $locked->save();

            return $movement;
        });
    }
}
