<?php

namespace App\Http\Controllers;

use App\Actions\Secagens\ConcludeSecagemAction;
use App\Actions\Secagens\CreateSecagemAction;
use App\Http\Requests\Secagens\StoreSecagemItemRequest;
use App\Http\Requests\Secagens\StoreSecagemRequest;
use App\Models\Area;
use App\Models\Customer;
use App\Models\Dryer;
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
            ->with('user', 'dryer')
            ->withCount('items')
            ->orderByDesc('data')
            ->orderByDesc('numero')
            ->paginate(20);

        return view('secagens.index', compact('secagens'));
    }

    public function create(): View
    {
        $this->authorize('create', Secagem::class);
        $dryers = Dryer::ativo()->orderBy('nome')->get(['id', 'nome']);

        if ($dryers->isEmpty()) {
            return view('secagens.no-dryer');
        }

        $areas = Area::ativo()->orderBy('nome')->get(['id', 'nome']);

        return view('secagens.create', compact('dryers', 'areas'));
    }

    public function store(StoreSecagemRequest $request, CreateSecagemAction $action): RedirectResponse
    {
        $secagem = $action->execute($request->user(), $request->validated());
        return redirect()->route('secagens.edit', $secagem)
            ->with('flash', '<strong>Secagem #' . $secagem->numero . '</strong> criada como rascunho.');
    }

    public function show(Secagem $secagem): View
    {
        $this->ensureSameFarm($secagem);
        $this->authorize('view', $secagem);
        $secagem->load('items.customer', 'user', 'dryer', 'area');

        return view('secagens.show', compact('secagem'));
    }

    public function edit(Secagem $secagem): View
    {
        $this->ensureSameFarm($secagem);
        $this->authorize('update', $secagem);
        $secagem->load('items.customer', 'dryer', 'area');
        $customers = Customer::orderBy('nome')->get(['id', 'nome', 'saldo_cafe_kg']);
        $dryers = Dryer::ativo()->orderBy('nome')->get(['id', 'nome']);
        // Inclui o secador atual se ele estiver inativo
        if ($secagem->dryer && ! $secagem->dryer->ativo) {
            $dryers->push($secagem->dryer->only(['id', 'nome']));
        }
        $areas = Area::ativo()->orderBy('nome')->get(['id', 'nome']);
        // Inclui a área atual se ela estiver inativa (pra não desvincular sem querer)
        if ($secagem->area && ! $secagem->area->ativo) {
            $areas->push($secagem->area->only(['id', 'nome']));
        }

        return view('secagens.edit', compact('secagem', 'customers', 'dryers', 'areas'));
    }

    public function update(StoreSecagemRequest $request, Secagem $secagem): RedirectResponse
    {
        $this->ensureSameFarm($secagem);
        $this->authorize('update', $secagem);

        $secagem->update($request->validated());
        return redirect()->route('secagens.edit', $secagem)
            ->with('flash', '<strong>Secagem #' . $secagem->numero . '</strong> atualizada.');
    }

    public function destroy(Secagem $secagem): RedirectResponse
    {
        $this->ensureSameFarm($secagem);
        $this->authorize('delete', $secagem);
        $numero = $secagem->numero;
        $secagem->delete();

        return redirect()->route('secagens.index')
            ->with('flash', '<strong>Secagem #' . $numero . '</strong> excluída.');
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

        return redirect()->route('secagens.edit', $secagem)
            ->with('flash', 'Item adicionado à secagem.');
    }

    public function destroyItem(Secagem $secagem, SecagemItem $item): RedirectResponse
    {
        $this->ensureSameFarm($secagem);
        $this->authorize('update', $secagem);
        if ($item->secagem_id !== $secagem->id) {
            throw new AuthorizationException();
        }
        $item->delete();
        return redirect()->route('secagens.edit', $secagem)
            ->with('flash', 'Item removido da secagem.');
    }

    public function pdf(Secagem $secagem): Response
    {
        $this->ensureSameFarm($secagem);
        $this->authorize('view', $secagem);
        $secagem->load('items.customer', 'farm', 'dryer');

        $pdf = Pdf::loadView('secagens.pdf', compact('secagem'))->setPaper('a4', 'landscape');
        return $pdf->download("secagem-{$secagem->numero}.pdf");
    }

    public function conclude(Secagem $secagem, ConcludeSecagemAction $action): RedirectResponse
    {
        $this->ensureSameFarm($secagem);
        $this->authorize('conclude', $secagem);

        $action->execute($secagem, auth()->user());

        return redirect()->route('secagens.show', $secagem)
            ->with('flash', '<strong>Secagem #' . $secagem->numero . '</strong> concluída. Saldos dos clientes atualizados.');
    }

    private function ensureSameFarm(Secagem $secagem): void
    {
        $user = auth()->user();
        if ($user && ! $user->isRoot() && $secagem->farm_id !== $user->farm_id) {
            throw new AuthorizationException();
        }
    }
}
