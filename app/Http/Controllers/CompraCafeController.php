<?php

namespace App\Http\Controllers;

use App\Actions\Compras\RegisterCompraCafeAction;
use App\Exceptions\DomainException;
use App\Http\Requests\Compras\StoreCompraCafeRequest;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Farm;
use App\Models\Movement;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Compras de café feitas pelo dono da fazenda (Juvenal, etc.) pra revender.
 *
 * Cada compra gera:
 *  - Expense em categoria reservada "Compra de café"
 *  - Movement de entrada na Farm (tipo=compra, source=Expense)
 *
 * Não tem edit/destroy nessa v1 — pra corrigir, ajuste manual no extrato
 * + edição direta da despesa.
 */
class CompraCafeController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Expense::class);

        $categoria = ExpenseCategory::query()->where('nome', ExpenseCategory::COMPRA_CAFE)->first();

        $compras = collect();
        if ($categoria) {
            $compras = Expense::query()
                ->where('expense_category_id', $categoria->id)
                ->with(['user:id,name'])
                ->orderByDesc('data')
                ->orderByDesc('id')
                ->paginate(20);

            $expenseIds = $compras->pluck('id')->all();
            $movsBySource = Movement::query()
                ->where('source_type', (new Expense)->getMorphClass())
                ->whereIn('source_id', $expenseIds)
                ->get()
                ->keyBy('source_id');

            $compras->getCollection()->transform(function (Expense $e) use ($movsBySource) {
                $e->setAttribute('movement', $movsBySource->get($e->id));
                return $e;
            });
        }

        return view('compras.index', compact('compras'));
    }

    public function create(): View
    {
        $this->authorize('create', Expense::class);
        return view('compras.create');
    }

    public function store(StoreCompraCafeRequest $request, RegisterCompraCafeAction $action): RedirectResponse
    {
        $user = $request->user();
        $farm = Farm::query()->where('id', $user->farm_id)->firstOrFail();

        try {
            $action->execute($farm, $user, $request->validated());
        } catch (DomainException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        $produtoLabel = mb_strtolower(\App\Support\StatusLabels::produto($request->validated('produto')));
        $qtde = number_format((float) $request->validated('quantidade_kg'), 2, ',', '.');
        $total = number_format((float) $request->validated('valor_total'), 2, ',', '.');

        return redirect()->route('compras.index')
            ->with('flash', "Compra registrada: <strong>{$qtde} kg</strong> de {$produtoLabel} por R$ {$total}.");
    }

    private function ensureSameFarm(Expense $expense): void
    {
        $user = auth()->user();
        if ($user && ! $user->isRoot() && $expense->farm_id !== $user->farm_id) {
            throw new AuthorizationException();
        }
    }
}
