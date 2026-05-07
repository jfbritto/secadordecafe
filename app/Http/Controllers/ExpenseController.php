<?php

namespace App\Http\Controllers;

use App\Http\Requests\Expenses\StoreExpenseRequest;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Expense::class);

        $from = $request->string('from')->toString() ?: null;
        $to = $request->string('to')->toString() ?: null;
        $catId = $request->integer('cat') ?: null;

        $base = Expense::query()->between($from, $to)->category($catId);

        $totals = (clone $base)
            ->reorder()
            ->join('expense_categories', 'expenses.expense_category_id', '=', 'expense_categories.id')
            ->selectRaw('expense_categories.id as cat_id, expense_categories.nome as cat_nome, SUM(expenses.valor_total) as total, COUNT(*) as qtd')
            ->groupBy('expense_categories.id', 'expense_categories.nome')
            ->orderBy('expense_categories.nome')
            ->get()
            ->keyBy('cat_id');

        $expenses = $base->with('category')->orderByDesc('data')->paginate(20)->withQueryString();

        $totalGeral = $totals->sum('total');

        // Lista de categorias para o filtro: todas (ativas + inativas em uso)
        $allCategories = ExpenseCategory::query()->orderBy('nome')->get(['id', 'nome', 'ativo']);

        return view('expenses.index', compact(
            'expenses', 'from', 'to', 'catId', 'totals', 'totalGeral', 'allCategories'
        ));
    }

    public function create(): View
    {
        $this->authorize('create', Expense::class);
        $categories = ExpenseCategory::ativo()->orderBy('nome')->get(['id', 'nome']);

        if ($categories->isEmpty()) {
            return view('expenses.no-category');
        }

        return view('expenses.create', compact('categories'));
    }

    public function store(StoreExpenseRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;
        Expense::create($data);

        return redirect()->route('despesas.index')
            ->with('flash', '<strong>R$ ' . number_format((float) $data['valor_total'], 2, ',', '.') . '</strong> · ' . e($data['descricao']) . ' registrada.');
    }

    public function edit(Expense $despesa): View
    {
        $this->ensureSameFarm($despesa);
        $this->authorize('update', $despesa);

        $categories = ExpenseCategory::ativo()->orderBy('nome')->get(['id', 'nome']);
        // Inclui a categoria atual mesmo se inativa
        if ($despesa->category && ! $despesa->category->ativo) {
            $categories->push((object) ['id' => $despesa->category->id, 'nome' => $despesa->category->nome.' (inativa)']);
        }

        return view('expenses.edit', ['expense' => $despesa, 'categories' => $categories]);
    }

    public function update(StoreExpenseRequest $request, Expense $despesa): RedirectResponse
    {
        $this->ensureSameFarm($despesa);
        $despesa->update($request->validated());
        return redirect()->route('despesas.index')
            ->with('flash', '<strong>' . e($despesa->descricao) . '</strong> atualizada.');
    }

    public function destroy(Expense $despesa): RedirectResponse
    {
        $this->ensureSameFarm($despesa);
        $this->authorize('delete', $despesa);
        $descricao = $despesa->descricao;
        $despesa->delete();
        return redirect()->route('despesas.index')
            ->with('flash', '<strong>' . e($descricao) . '</strong> excluída.');
    }

    private function ensureSameFarm(Expense $expense): void
    {
        $user = auth()->user();
        if ($user && ! $user->isRoot() && $expense->farm_id !== $user->farm_id) {
            throw new AuthorizationException();
        }
    }
}
