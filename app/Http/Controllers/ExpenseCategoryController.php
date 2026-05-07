<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExpenseCategories\StoreExpenseCategoryRequest;
use App\Models\ExpenseCategory;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ExpenseCategoryController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', ExpenseCategory::class);

        $categories = ExpenseCategory::query()
            ->withCount('expenses')
            ->orderByDesc('ativo')
            ->orderBy('nome')
            ->paginate(30);

        return view('expense-categories.index', compact('categories'));
    }

    public function create(): View
    {
        $this->authorize('create', ExpenseCategory::class);
        return view('expense-categories.create');
    }

    public function store(StoreExpenseCategoryRequest $request): RedirectResponse
    {
        $cat = ExpenseCategory::create($request->validated());
        return redirect()->route('despesas.categorias.index')
            ->with('flash', '<strong>' . e($cat->nome) . '</strong> cadastrada.');
    }

    public function edit(ExpenseCategory $categoria): View
    {
        $this->ensureSameFarm($categoria);
        $this->authorize('update', $categoria);
        return view('expense-categories.edit', ['category' => $categoria]);
    }

    public function update(StoreExpenseCategoryRequest $request, ExpenseCategory $categoria): RedirectResponse
    {
        $this->ensureSameFarm($categoria);
        $categoria->update($request->validated());
        return redirect()->route('despesas.categorias.index')
            ->with('flash', '<strong>' . e($categoria->nome) . '</strong> atualizada.');
    }

    public function destroy(ExpenseCategory $categoria): RedirectResponse
    {
        $this->ensureSameFarm($categoria);
        $this->authorize('delete', $categoria);

        if ($categoria->expenses()->exists()) {
            return redirect()->route('despesas.categorias.index')
                ->with('error', 'Não é possível excluir <strong>' . e($categoria->nome) . '</strong>: há despesas vinculadas. Inative-a em vez de excluir.');
        }

        $nome = $categoria->nome;
        $categoria->delete();

        return redirect()->route('despesas.categorias.index')
            ->with('flash', '<strong>' . e($nome) . '</strong> excluída.');
    }

    private function ensureSameFarm(ExpenseCategory $cat): void
    {
        $user = auth()->user();
        if ($user && ! $user->isRoot() && $cat->farm_id !== $user->farm_id) {
            throw new AuthorizationException();
        }
    }
}
