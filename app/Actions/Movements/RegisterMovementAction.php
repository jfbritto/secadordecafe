<?php

namespace App\Actions\Movements;

use App\Exceptions\DomainException;
use App\Models\Customer;
use App\Models\Movement;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class RegisterMovementAction
{
    /**
     * Cria movimentação e atualiza saldo do cliente sob lock.
     *
     * @param string $tipo  one of entrada|saida|ajuste|secagem
     * @param string $direcao  '+' ou '-' (apenas para ajuste; ignorado para os demais)
     * @param float  $quantidade  sempre positivo (módulo)
     * @param Model  $source  origem polimórfica da movimentação (ex.: Secagem)
     */
    public function execute(
        Customer $customer,
        User $user,
        string $tipo,
        float $quantidade,
        ?string $observacao = null,
        ?string $direcao = null,
        ?\DateTimeInterface $occurredAt = null,
        ?Model $source = null,
    ): Movement {
        if ($quantidade <= 0) {
            throw new DomainException('Quantidade deve ser maior que zero.');
        }

        $signed = match ($tipo) {
            Movement::TIPO_ENTRADA => +$quantidade,
            Movement::TIPO_SAIDA => -$quantidade,
            Movement::TIPO_SECAGEM => -$quantidade,
            Movement::TIPO_AJUSTE => $direcao === '-' ? -$quantidade : +$quantidade,
            default => throw new DomainException("Tipo inválido: {$tipo}"),
        };

        return DB::transaction(function () use ($customer, $user, $tipo, $signed, $observacao, $occurredAt, $source) {
            $locked = Customer::query()->withoutGlobalScopes()->whereKey($customer->id)->lockForUpdate()->first();
            if (! $locked) {
                throw new DomainException('Cliente não encontrado.');
            }

            // Defesa em profundidade: cliente DEVE ser da mesma fazenda do usuário.
            // Os controllers já filtram via route binding + global scope, mas se uma action
            // interna passar um Customer de outra farm por engano, falha aqui em vez de mexer no saldo.
            if (! $user->isRoot() && $locked->farm_id !== $user->farm_id) {
                throw new DomainException('Cliente fora da fazenda atual.');
            }

            // Source (Secagem etc.) também precisa pertencer à mesma farm.
            if ($source && isset($source->farm_id) && $source->farm_id !== $locked->farm_id) {
                throw new DomainException('Origem da movimentação fora da fazenda do cliente.');
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
                'source_type' => $source?->getMorphClass(),
                'source_id' => $source?->getKey(),
                'occurred_at' => $occurredAt ?? now(),
            ]);

            $locked->saldo_cafe_kg = $newSaldo;
            $locked->save();

            return $movement;
        });
    }
}
