<?php

namespace App\Actions\Movements;

use App\Exceptions\DomainException;
use App\Models\Concerns\HasStockMovements;
use App\Models\Farm;
use App\Models\Movement;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class RegisterMovementAction
{
    /**
     * Cria movimentação polimórfica e atualiza o saldo do owner sob lock.
     *
     * Owner pode ser Customer, Area ou Farm (qualquer model que use HasStockMovements).
     * Produto é 'coco' ou 'seco' — define qual coluna de saldo do owner é mutada.
     *
     * Regras de sinal por tipo:
     *   - entrada, colheita, producao, comissao → soma
     *   - saida, secagem                         → subtrai
     *   - ajuste                                  → soma ou subtrai conforme `direcao`
     *
     * @param Model&HasStockMovements $owner
     * @param string $produto  'coco' | 'seco'
     * @param string $tipo     entrada | saida | ajuste | secagem | colheita | producao | comissao
     * @param float  $quantidade  sempre positivo (módulo)
     * @param string $direcao  '+' ou '-' (apenas para ajuste)
     * @param Model  $source  origem polimórfica (ex.: Secagem)
     */
    public function execute(
        Model $owner,
        User $user,
        string $tipo,
        string $produto,
        float $quantidade,
        ?string $observacao = null,
        ?string $direcao = null,
        ?\DateTimeInterface $occurredAt = null,
        ?Model $source = null,
    ): Movement {
        if ($quantidade <= 0) {
            throw new DomainException('Quantidade deve ser maior que zero.');
        }

        if (! in_array($produto, [Movement::PRODUTO_COCO, Movement::PRODUTO_SECO], true)) {
            throw new DomainException("Produto inválido: {$produto}");
        }

        $signed = match ($tipo) {
            Movement::TIPO_ENTRADA,
            Movement::TIPO_COLHEITA,
            Movement::TIPO_PRODUCAO,
            Movement::TIPO_COMISSAO => +$quantidade,

            Movement::TIPO_SAIDA,
            Movement::TIPO_SECAGEM => -$quantidade,

            Movement::TIPO_AJUSTE => $direcao === '-' ? -$quantidade : +$quantidade,

            default => throw new DomainException("Tipo inválido: {$tipo}"),
        };

        return DB::transaction(function () use ($owner, $user, $tipo, $produto, $signed, $observacao, $occurredAt, $source) {
            // Lock pessimista do owner. Farm não tem global scope; demais tipos usam scope farm,
            // por isso withoutGlobalScopes() pra evitar query duplicada.
            $ownerClass = get_class($owner);
            $locked = $ownerClass::query()->withoutGlobalScopes()->whereKey($owner->getKey())->lockForUpdate()->first();
            if (! $locked) {
                throw new DomainException(class_basename($ownerClass) . ' não encontrado.');
            }

            // Defesa em profundidade: owner tem que estar na fazenda do usuário (exceto Farm,
            // que é o próprio escopo).
            $ownerFarmId = $locked instanceof Farm ? $locked->id : ($locked->farm_id ?? null);
            if (! $user->isRoot() && $ownerFarmId !== $user->farm_id) {
                throw new DomainException('Owner fora da fazenda atual.');
            }

            // Source também precisa pertencer à mesma farm
            if ($source && isset($source->farm_id) && $source->farm_id !== $ownerFarmId) {
                throw new DomainException('Origem da movimentação fora da fazenda do owner.');
            }

            $novoSaldo = $locked->incrementSaldo($produto, $signed);
            if ($novoSaldo < 0) {
                throw new DomainException("Saldo de {$produto} insuficiente para esta operação.");
            }

            $movement = Movement::create([
                'farm_id' => $ownerFarmId,
                'owner_type' => $locked->getMorphClass(),
                'owner_id' => $locked->getKey(),
                'user_id' => $user->id,
                'tipo' => $tipo,
                'produto' => $produto,
                'quantidade_kg' => $signed,
                'observacao' => $observacao,
                'source_type' => $source?->getMorphClass(),
                'source_id' => $source?->getKey(),
                'occurred_at' => $occurredAt ?? now(),
            ]);

            $locked->save();

            return $movement;
        });
    }
}
