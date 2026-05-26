<?php

namespace App\Http\Controllers;

use App\Actions\Movements\RegisterMovementAction;
use App\Http\Requests\Movements\StoreMovementRequest;
use App\Models\Area;
use App\Models\Customer;
use App\Models\Farm;
use App\Models\Movement;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Extrato e lançamento manual de movimentações.
 *
 * Owner polimórfico:
 *  - /movimentacoes/cliente/{id}
 *  - /movimentacoes/area/{id}
 *  - /movimentacoes/fazenda
 *
 * Filtro opcional de produto via querystring: ?produto=coco|seco
 */
class MovementController extends Controller
{
    public function indexFazenda(Request $request): View
    {
        return $this->renderIndex($request, 'fazenda', null);
    }

    public function storeFazenda(StoreMovementRequest $request, RegisterMovementAction $action): RedirectResponse
    {
        return $this->handleStore($request, $action, 'fazenda', null);
    }

    public function index(string $tipo, int $id, Request $request): View
    {
        return $this->renderIndex($request, $tipo, $id);
    }

    public function store(StoreMovementRequest $request, RegisterMovementAction $action, string $tipo, int $id): RedirectResponse
    {
        return $this->handleStore($request, $action, $tipo, $id);
    }

    private function renderIndex(Request $request, string $tipo, ?int $id): View
    {
        $owner = $this->resolveOwner($tipo, $id);
        $this->authorizeOwner($owner);

        $produto = $request->string('produto')->toString() ?: null;

        // Pra Area: histórico filtrado por movements.area_id (Area não é dona,
        // a Farm é). Saldo "após cada movimento" não faz sentido pra Area, então
        // pulamos o running balance.
        $isAreaHistorico = $owner instanceof Area;

        if ($isAreaHistorico) {
            $query = Movement::query()
                ->where('area_id', $owner->id)
                ->where('farm_id', $owner->farm_id);
        } else {
            $query = $owner->movements();
        }

        $query->with(['user', 'source', 'area'])
            ->latest('occurred_at');

        if ($produto) {
            $query->where('produto', $produto);
        }

        $movements = $query->paginate(30)->withQueryString();

        if (! $isAreaHistorico && $movements->isNotEmpty()) {
            $this->attachRunningBalance($movements, $owner, $produto);
        }

        return view('movements.index', [
            'owner' => $owner,
            'ownerLabel' => $this->ownerLabel($owner),
            'ownerKind' => $tipo,
            'isAreaHistorico' => $isAreaHistorico,
            'movements' => $movements,
            'produto' => $produto,
        ]);
    }

    private function handleStore(StoreMovementRequest $request, RegisterMovementAction $action, string $tipo, ?int $id): RedirectResponse
    {
        if ($tipo === 'area') {
            // Area não tem saldo próprio; use Colheita pra entrada e o estoque da
            // fazenda pra saída/ajuste. Vamos pra fazenda com flash explicativo.
            return redirect()->route('movimentacoes.fazenda.index')
                ->with('flash', 'Use o estoque da fazenda pra registrar entradas/saídas. Colheita de área tem tela própria.');
        }

        $owner = $this->resolveOwner($tipo, $id);
        $this->authorizeOwner($owner);

        $action->execute(
            owner: $owner,
            user: $request->user(),
            tipo: $request->validated('tipo'),
            produto: $request->validated('produto'),
            quantidade: (float) $request->validated('quantidade'),
            observacao: $request->validated('observacao'),
            direcao: $request->validated('direcao'),
            occurredAt: $request->validated('occurred_at')
                ? new \DateTime($request->validated('occurred_at'))
                : null,
        );

        $owner->refresh();

        if ($tipo === 'fazenda') {
            return redirect()->route('movimentacoes.fazenda.index')
                ->with('flash', 'Movimentação registrada.');
        }

        return redirect()->route('movimentacoes.index', ['tipo' => $tipo, 'id' => $id])
            ->with('flash', 'Movimentação registrada.');
    }

    /**
     * Resolve o owner polimórfico a partir do slug da rota (cliente|area|fazenda).
     */
    private function resolveOwner(string $tipo, ?int $id): Model
    {
        return match ($tipo) {
            'cliente' => Customer::query()->findOrFail($id),
            'area'    => Area::query()->findOrFail($id),
            'fazenda' => Farm::query()->where('id', auth()->user()->farm_id)->firstOrFail(),
            default   => throw new AuthorizationException("Tipo de owner inválido: {$tipo}"),
        };
    }

    private function authorizeOwner(Model $owner): void
    {
        $user = auth()->user();
        if (! $user) {
            throw new AuthorizationException();
        }
        $ownerFarmId = $owner instanceof Farm ? $owner->id : $owner->farm_id;
        if (! $user->isRoot() && $ownerFarmId !== $user->farm_id) {
            throw new AuthorizationException();
        }
    }

    private function ownerLabel(Model $owner): string
    {
        if ($owner instanceof Customer) return $owner->nome;
        if ($owner instanceof Area)     return $owner->nome;
        if ($owner instanceof Farm)     return $owner->nome . ' (estoque da fazenda)';
        return '—';
    }

    /**
     * Calcula `saldo_apos` em memória pra cada movimento da página.
     * Considera apenas o produto do filtro atual (se houver). Sem filtro, soma todos.
     */
    private function attachRunningBalance($movements, Model $owner, ?string $produto): void
    {
        $oldestOnPage = $movements->last();

        $baseQuery = Movement::query()
            ->where('owner_type', $owner->getMorphClass())
            ->where('owner_id', $owner->getKey())
            ->where(function ($q) use ($oldestOnPage) {
                $q->where('occurred_at', '<', $oldestOnPage->occurred_at)
                  ->orWhere(function ($q2) use ($oldestOnPage) {
                      $q2->where('occurred_at', $oldestOnPage->occurred_at)
                         ->where('id', '<', $oldestOnPage->id);
                  });
            });

        if ($produto) {
            $baseQuery->where('produto', $produto);
        }

        $somaAntes = (float) $baseQuery->sum('quantidade_kg');

        $running = $somaAntes;
        $sortedAsc = $movements->getCollection()
            ->sortBy(fn ($m) => sprintf('%s-%020d', $m->occurred_at->format('YmdHis'), $m->id))
            ->values();

        foreach ($sortedAsc as $m) {
            $running += (float) $m->quantidade_kg;
            $m->saldo_apos = $running;
        }
    }
}
