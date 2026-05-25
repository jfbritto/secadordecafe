<?php

namespace App\Models\Concerns;

use App\Models\Movement;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Trait pra owners de estoque (Customer, Area, Farm). Define a relação
 * polimórfica com Movement e helpers de incremento de saldo por produto.
 *
 * O model que usa essa trait DEVE expor os métodos saldoColumnFor(string $produto)
 * que retorna o nome da coluna onde o saldo daquele produto vive. Isso permite
 * cada owner ter colunas com nomes próprios (Customer e Area têm coco+seco,
 * Farm só tem seco de comissão).
 */
trait HasStockMovements
{
    public function movements(): MorphMany
    {
        return $this->morphMany(Movement::class, 'owner');
    }

    /**
     * Incrementa o saldo na coluna correspondente ao produto, mutando o model.
     * Retorna o novo saldo. NÃO persiste — chamador deve fazer save() em transação.
     */
    public function incrementSaldo(string $produto, float $signed): float
    {
        $column = $this->saldoColumnFor($produto);
        $novo = round((float) $this->{$column} + $signed, 2);
        $this->{$column} = $novo;
        return $novo;
    }

    public function getSaldo(string $produto): float
    {
        return (float) $this->{$this->saldoColumnFor($produto)};
    }
}
