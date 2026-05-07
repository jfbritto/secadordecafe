<?php

namespace App\Http\Controllers;

use App\Actions\Secagens\ConcludeSecagemAction;
use App\Actions\Secagens\CreateSecagemAction;
use App\Http\Requests\Secagens\StoreSecagemItemRequest;
use App\Http\Requests\Secagens\StoreSecagemRequest;
use App\Models\Customer;
use App\Models\Secagem;
use App\Models\SecagemItem;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class SecagemController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Secagem::class);

        $secagens = Secagem::query()
            ->with('user')
            ->withCount('items')
            ->orderByDesc('data')
            ->orderByDesc('numero')
            ->paginate(20);

        return view('secagens.index', compact('secagens'));
    }

    public function create(): View
    {
        $this->authorize('create', Secagem::class);
        return view('secagens.create');
    }

    public function store(StoreSecagemRequest $request, CreateSecagemAction $action): RedirectResponse
    {
        $secagem = $action->execute($request->user(), $request->validated());
        return redirect()->route('secagens.edit', $secagem)
            ->with('flash', "Secagem #{$secagem->numero} criada (rascunho).");
    }

    public function show(Secagem $secagem): View
    {
        $this->ensureSameFarm($secagem);
        $this->authorize('view', $secagem);
        $secagem->load('items.customer', 'user');

        return view('secagens.show', compact('secagem'));
    }

    public function edit(Secagem $secagem): View
    {
        $this->ensureSameFarm($secagem);
        $this->authorize('update', $secagem);
        $secagem->load('items.customer');
        $customers = Customer::orderBy('nome')->get(['id', 'nome', 'saldo_cafe_kg']);

        return view('secagens.edit', compact('secagem', 'customers'));
    }

    public function update(StoreSecagemRequest $request, Secagem $secagem): RedirectResponse
    {
        $this->ensureSameFarm($secagem);
        $this->authorize('update', $secagem);

        $secagem->update($request->validated());
        return redirect()->route('secagens.edit', $secagem)->with('flash', 'Secagem atualizada.');
    }

    public function destroy(Secagem $secagem): RedirectResponse
    {
        $this->ensureSameFarm($secagem);
        $this->authorize('delete', $secagem);
        $numero = $secagem->numero;
        $secagem->delete();

        return redirect()->route('secagens.index')->with('flash', "Secagem #{$numero} excluída.");
    }

    public function storeItem(StoreSecagemItemRequest $request, Secagem $secagem): RedirectResponse
    {
        $this->ensureSameFarm($secagem);
        $this->authorize('update', $secagem);

        $data = $request->validated();
        $seca = (float) $data['quantidade_seca_kg'];
        $perc = (float) ($data['comissao_percentual'] ?? 0);
        $comissao = SecagemItem::calcularComissao($seca, $perc);
        $liquido = SecagemItem::calcularSaldoLiquido($seca, $comissao);

        SecagemItem::create([
            'farm_id' => $secagem->farm_id,
            'secagem_id' => $secagem->id,
            'customer_id' => $data['customer_id'],
            'quantidade_recebida_kg' => $data['quantidade_recebida_kg'],
            'quantidade_seca_kg' => $seca,
            'comissao_percentual' => $perc,
            'comissao_kg' => $comissao,
            'saldo_liquido_kg' => $liquido,
        ]);

        return redirect()->route('secagens.edit', $secagem)->with('flash', 'Item adicionado.');
    }

    public function destroyItem(Secagem $secagem, SecagemItem $item): RedirectResponse
    {
        $this->ensureSameFarm($secagem);
        $this->authorize('update', $secagem);
        if ($item->secagem_id !== $secagem->id) {
            throw new AuthorizationException();
        }
        $item->delete();
        return redirect()->route('secagens.edit', $secagem)->with('flash', 'Item removido.');
    }

    public function pdf(Secagem $secagem): Response
    {
        $this->ensureSameFarm($secagem);
        $this->authorize('view', $secagem);
        $secagem->load('items.customer', 'farm');

        $pdf = Pdf::loadView('secagens.pdf', compact('secagem'))->setPaper('a4', 'landscape');
        return $pdf->download("secagem-{$secagem->numero}.pdf");
    }

    public function conclude(Secagem $secagem, ConcludeSecagemAction $action): RedirectResponse
    {
        $this->ensureSameFarm($secagem);
        $this->authorize('conclude', $secagem);

        $action->execute($secagem, auth()->user());

        return redirect()->route('secagens.show', $secagem)
            ->with('flash', "Secagem #{$secagem->numero} concluída.");
    }

    private function ensureSameFarm(Secagem $secagem): void
    {
        $user = auth()->user();
        if ($user && ! $user->isRoot() && $secagem->farm_id !== $user->farm_id) {
            throw new AuthorizationException();
        }
    }
}
