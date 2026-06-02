<?php

namespace App\Actions\Compras;

use App\Actions\Movements\RegisterMovementAction;
use App\Http\Controllers\DashboardController;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Farm;
use App\Models\Movement;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Lança a compra de café feita pelo dono da fazenda.
 *
 * Gera dois efeitos atômicos:
 *  1. Expense na categoria "Compra de café" (despesa do caixa)
 *  2. Movement de entrada na Farm (estoque próprio cresce)
 *
 * O Movement aponta pra Expense via `source`, então o extrato linka direto
 * pra despesa que originou a entrada.
 */
class RegisterCompraCafeAction
{
    public function __construct(private RegisterMovementAction $registerMovement)
    {
    }

    /**
     * @param array{
     *     data: string,
     *     produto: string,
     *     quantidade_kg: float|string,
     *     valor_unitario: float|string,
     *     valor_total: float|string,
     *     fornecedor?: ?string,
     *     observacoes?: ?string,
     * } $data
     * @return array{expense: Expense, movement: Movement}
     */
    public function execute(Farm $farm, User $user, array $data): array
    {
        $produto = $data['produto'];
        $quantidadeKg = (float) $data['quantidade_kg'];
        $valorTotal = (float) $data['valor_total'];
        // Valor unitário é opcional — se não veio, deriva do total (e evita divisão por zero).
        $valorUnitario = isset($data['valor_unitario']) && $data['valor_unitario'] !== '' && $data['valor_unitario'] !== null
            ? (float) $data['valor_unitario']
            : ($quantidadeKg > 0 ? round($valorTotal / $quantidadeKg, 2) : 0.0);
        $fornecedor = $data['fornecedor'] ?? null;
        $observacoes = $data['observacoes'] ?? null;
        $dataCompra = $data['data'];

        return DB::transaction(function () use ($farm, $user, $produto, $quantidadeKg, $valorUnitario, $valorTotal, $fornecedor, $observacoes, $dataCompra) {
            $categoria = ExpenseCategory::query()
                ->withoutGlobalScopes()
                ->firstOrCreate(
                    ['farm_id' => $farm->id, 'nome' => ExpenseCategory::COMPRA_CAFE],
                    ['ativo' => true]
                );

            $produtoLabel = mb_strtolower(\App\Support\StatusLabels::produto($produto));
            $descricao = trim(sprintf(
                'Compra de %s kg de %s%s',
                number_format($quantidadeKg, 2, ',', '.'),
                $produtoLabel,
                $fornecedor ? ' — ' . $fornecedor : ''
            ));

            $expense = Expense::create([
                'farm_id' => $farm->id,
                'user_id' => $user->id,
                'expense_category_id' => $categoria->id,
                'data' => $dataCompra,
                'descricao' => $descricao,
                'unidade' => 'kg',
                'quantidade' => $quantidadeKg,
                'valor_unitario' => $valorUnitario,
                'valor_total' => $valorTotal,
                'observacoes' => $observacoes,
            ]);

            $movement = $this->registerMovement->execute(
                owner: $farm,
                user: $user,
                tipo: Movement::TIPO_COMPRA,
                produto: $produto,
                quantidade: $quantidadeKg,
                observacao: $fornecedor ? "Compra de café — {$fornecedor}" : 'Compra de café',
                occurredAt: new \DateTimeImmutable($dataCompra),
                source: $expense,
            );

            Cache::forget(DashboardController::farmMetricsCacheKey($farm->id));

            return ['expense' => $expense, 'movement' => $movement];
        });
    }
}
