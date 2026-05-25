<?php

namespace App\Http\Controllers;

use App\Http\Requests\Areas\StoreAreaRequest;
use App\Models\Area;
use App\Models\Movement;
use App\Models\Secagem;
use App\Models\SecagemItem;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AreaController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Area::class);

        $areas = Area::query()
            ->withCount(['secagemItems as itens_count'])
            ->orderByDesc('ativo')
            ->orderBy('nome')
            ->paginate(20);

        return view('areas.index', compact('areas'));
    }

    public function create(): View
    {
        $this->authorize('create', Area::class);
        return view('areas.create');
    }

    public function store(StoreAreaRequest $request): RedirectResponse
    {
        $area = Area::create($request->validated());
        return redirect()->route('areas.index')
            ->with('flash', '<strong>' . e($area->nome) . '</strong> cadastrada.');
    }

    public function show(Area $area, Request $request): View
    {
        $this->ensureSameFarm($area);
        $this->authorize('view', $area);

        // Filtro de período: default = "este ano"
        $periodo = $request->string('periodo', 'ano')->toString();
        [$de, $ate, $periodoLabel] = $this->resolvePeriod($periodo, $request);

        // Items desta área dentro do período (via secagens concluídas)
        $itemsQuery = SecagemItem::query()
            ->where('origin_type', Area::class)
            ->where('origin_id', $area->id)
            ->whereHas('secagem', function ($q) use ($de, $ate) {
                $q->where('status', Secagem::STATUS_CONCLUIDA);
                if ($de) $q->whereDate('data', '>=', $de);
                if ($ate) $q->whereDate('data', '<=', $ate);
            });

        $itemStats = (clone $itemsQuery)->selectRaw('
            COALESCE(SUM(quantidade_recebida_kg), 0) as total_recebido,
            COALESCE(SUM(quantidade_seca_kg), 0) as total_seco,
            COALESCE(SUM(comissao_kg), 0) as total_comissao,
            COALESCE(SUM(saldo_liquido_kg), 0) as total_liquido,
            COUNT(*) as total_itens,
            COUNT(DISTINCT secagem_id) as total_secagens
        ')->first();

        $stats = [
            'qtd_secagens' => (int) ($itemStats->total_secagens ?? 0),
            'total_recebido' => (float) ($itemStats->total_recebido ?? 0),
            'total_seco' => (float) ($itemStats->total_seco ?? 0),
            'total_comissao' => (float) ($itemStats->total_comissao ?? 0),
            'total_liquido' => (float) ($itemStats->total_liquido ?? 0),
            'total_itens' => (int) ($itemStats->total_itens ?? 0),
            'periodo_label' => $periodoLabel,
        ];

        $ultimasSecagens = Secagem::query()
            ->whereIn('id', (clone $itemsQuery)->select('secagem_id'))
            ->with(['dryer', 'items' => fn ($q) => $q->where('origin_type', Area::class)->where('origin_id', $area->id)])
            ->orderByDesc('data')
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        return view('areas.show', [
            'area' => $area,
            'stats' => $stats,
            'ultimasSecagens' => $ultimasSecagens,
            'periodo' => $periodo,
            'de' => $de,
            'ate' => $ate,
        ]);
    }

    public function edit(Area $area): View
    {
        $this->ensureSameFarm($area);
        $this->authorize('update', $area);
        return view('areas.edit', ['area' => $area]);
    }

    public function update(StoreAreaRequest $request, Area $area): RedirectResponse
    {
        $this->ensureSameFarm($area);
        $area->update($request->validated());
        return redirect()->route('areas.index')
            ->with('flash', '<strong>' . e($area->nome) . '</strong> atualizada.');
    }

    public function destroy(Area $area): RedirectResponse
    {
        $this->ensureSameFarm($area);
        $this->authorize('delete', $area);

        if ($area->secagemItems()->exists() || $area->movements()->exists()) {
            return redirect()->route('areas.index')
                ->with('error', 'Não é possível excluir <strong>' . e($area->nome) . '</strong>: há movimentações ou itens de secagem vinculados. Inative-a em vez de excluir.');
        }

        $nome = $area->nome;
        $area->delete();

        return redirect()->route('areas.index')
            ->with('flash', '<strong>' . e($nome) . '</strong> excluída.');
    }

    private function ensureSameFarm(Area $area): void
    {
        $user = auth()->user();
        if ($user && ! $user->isRoot() && $area->farm_id !== $user->farm_id) {
            throw new AuthorizationException();
        }
    }

    /** @return array{0:?string,1:?string,2:string} */
    private function resolvePeriod(string $periodo, Request $request): array
    {
        return match ($periodo) {
            'mes' => [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString(), 'Este mês'],
            'tudo' => [null, null, 'Todo o histórico'],
            'custom' => [
                $request->string('de')->toString() ?: null,
                $request->string('ate')->toString() ?: null,
                'Período custom',
            ],
            default => [now()->startOfYear()->toDateString(), now()->endOfYear()->toDateString(), 'Este ano'],
        };
    }
}
