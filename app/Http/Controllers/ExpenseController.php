<?php

namespace App\Http\Controllers;

use App\Http\Requests\Expenses\StoreExpenseRequest;
use App\Models\Expense;
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
        $cat = $request->string('cat')->toString() ?: null;

        $query = Expense::query()->between($from, $to)->categoria($cat)->orderByDesc('data');

        $totals = (clone $query)
            ->selectRaw('categoria, SUM(valor_total) as total, COUNT(*) as qtd')
            ->groupBy('categoria')
            ->get()
            ->keyBy('categoria');

        $expenses = $query->paginate(20)->withQueryString();

        $totalGeral = $totals->sum('total');

        return view('expenses.index', compact('expenses', 'from', 'to', 'cat', 'totals', 'totalGeral'));
    }

    public function create(): View
    {
        $this->authorize('create', Expense::class);
        return view('expenses.create');
    }

    public function store(StoreExpenseRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;
        Expense::create($data);

        return redirect()->route('despesas.index')->with('flash', 'Despesa registrada.');
    }

    public function edit(Expense $despesa): View
    {
        $this->ensureSameFarm($despesa);
        $this->authorize('update', $despesa);
        return view('expenses.edit', ['expense' => $despesa]);
    }

    public function update(StoreExpenseRequest $request, Expense $despesa): RedirectResponse
    {
        $this->ensureSameFarm($despesa);
        $despesa->update($request->validated());
        return redirect()->route('despesas.index')->with('flash', 'Despesa atualizada.');
    }

    public function destroy(Expense $despesa): RedirectResponse
    {
        $this->ensureSameFarm($despesa);
        $this->authorize('delete', $despesa);
        $despesa->delete();
        return redirect()->route('despesas.index')->with('flash', 'Despesa excluída.');
    }

    private function ensureSameFarm(Expense $expense): void
    {
        $user = auth()->user();
        if ($user && ! $user->isRoot() && $expense->farm_id !== $user->farm_id) {
            throw new AuthorizationException();
        }
    }
}
