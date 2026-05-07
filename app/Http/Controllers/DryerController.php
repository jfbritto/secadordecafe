<?php

namespace App\Http\Controllers;

use App\Http\Requests\Dryers\StoreDryerRequest;
use App\Models\Dryer;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DryerController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Dryer::class);

        $dryers = Dryer::query()
            ->withCount('secagens')
            ->orderByDesc('ativo')
            ->orderBy('nome')
            ->paginate(20);

        return view('dryers.index', compact('dryers'));
    }

    public function create(): View
    {
        $this->authorize('create', Dryer::class);
        return view('dryers.create');
    }

    public function store(StoreDryerRequest $request): RedirectResponse
    {
        $dryer = Dryer::create($request->validated());
        return redirect()->route('secadores.index')
            ->with('flash', '<strong>' . e($dryer->nome) . '</strong> cadastrado.');
    }

    public function edit(Dryer $secador): View
    {
        $this->ensureSameFarm($secador);
        $this->authorize('update', $secador);
        return view('dryers.edit', ['dryer' => $secador]);
    }

    public function update(StoreDryerRequest $request, Dryer $secador): RedirectResponse
    {
        $this->ensureSameFarm($secador);
        $secador->update($request->validated());
        return redirect()->route('secadores.index')
            ->with('flash', '<strong>' . e($secador->nome) . '</strong> atualizado.');
    }

    public function destroy(Dryer $secador): RedirectResponse
    {
        $this->ensureSameFarm($secador);
        $this->authorize('delete', $secador);

        if ($secador->secagens()->exists()) {
            return redirect()->route('secadores.index')
                ->with('error', 'Não é possível excluir <strong>' . e($secador->nome) . '</strong>: há secagens vinculadas. Inative-o em vez de excluir.');
        }

        $nome = $secador->nome;
        $secador->delete();

        return redirect()->route('secadores.index')
            ->with('flash', '<strong>' . e($nome) . '</strong> excluído.');
    }

    private function ensureSameFarm(Dryer $dryer): void
    {
        $user = auth()->user();
        if ($user && ! $user->isRoot() && $dryer->farm_id !== $user->farm_id) {
            throw new AuthorizationException();
        }
    }
}
