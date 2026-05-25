<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\ChangeSubscriptionStatusAction;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Farm;
use App\Models\Movement;
use App\Models\Secagem;
use App\Models\Subscription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Painel ROOT pra gerenciar todas as fazendas da plataforma.
 * Acesso restrito a is_root — middleware aplicado nas rotas.
 */
class FarmController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()?->isRoot(), 403);

        $term = $request->string('q')->toString() ?: null;
        $statusFilter = $request->string('status')->toString() ?: null;

        $farms = Farm::query()
            ->with(['subscription', 'users:id,farm_id,name,email'])
            ->withCount(['users', 'customers' => fn ($q) => $q->withoutGlobalScopes()])
            ->when($term, function ($q) use ($term) {
                $like = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $term) . '%';
                $q->where(function ($q2) use ($like) {
                    $q2->where('nome', 'like', $like)
                       ->orWhereHas('users', fn ($q3) => $q3->where('email', 'like', $like));
                });
            })
            ->when($statusFilter, fn ($q) => $q->where('status', $statusFilter))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.farms.index', [
            'farms' => $farms,
            'term' => $term,
            'statusFilter' => $statusFilter,
            'statuses' => $this->statusOptions(),
        ]);
    }

    public function show(Request $request, Farm $farm): View
    {
        abort_unless($request->user()?->isRoot(), 403);

        $farm->load([
            'subscription',
            'users' => fn ($q) => $q->orderBy('name'),
            'users.roles',
        ]);

        // Contagens dentro do escopo da farm (sem global scope da farm atual do user logado)
        $counts = [
            'users' => $farm->users->count(),
            'customers' => Customer::withoutGlobalScopes()->where('farm_id', $farm->id)->count(),
            'secagens_concluidas' => Secagem::withoutGlobalScopes()
                ->where('farm_id', $farm->id)
                ->where('status', Secagem::STATUS_CONCLUIDA)
                ->count(),
            'secagens_rascunho' => Secagem::withoutGlobalScopes()
                ->where('farm_id', $farm->id)
                ->where('status', Secagem::STATUS_RASCUNHO)
                ->count(),
            'movimentos' => Movement::withoutGlobalScopes()->where('farm_id', $farm->id)->count(),
            'despesas' => Expense::withoutGlobalScopes()->where('farm_id', $farm->id)->count(),
            'saldo_total_kg' => (float) Customer::withoutGlobalScopes()
                ->where('farm_id', $farm->id)
                ->sum('saldo_coco_kg'),
        ];

        return view('admin.farms.show', [
            'farm' => $farm,
            'counts' => $counts,
            'statuses' => $this->statusOptions(),
            'transitions' => $this->availableTransitions($farm),
        ]);
    }

    public function changePlan(Request $request, Farm $farm, ChangeSubscriptionStatusAction $action): RedirectResponse
    {
        abort_unless($request->user()?->isRoot(), 403);

        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys($this->statusOptions()))],
        ]);

        $action->execute($farm, $data['status'], $request->user());

        return redirect()->route('admin.fazendas.show', $farm)
            ->with('flash', '<strong>' . e($farm->nome) . '</strong>: plano atualizado para <strong>' . e($this->statusOptions()[$data['status']]) . '</strong>.');
    }

    /** Labels de exibição. */
    private function statusOptions(): array
    {
        return [
            Subscription::STATUS_TRIAL    => 'Em teste',
            Subscription::STATUS_ACTIVE   => 'Ativa',
            Subscription::STATUS_PARTNER  => 'Parceira',
            Subscription::STATUS_PAST_DUE => 'Em atraso',
            Subscription::STATUS_BLOCKED  => 'Bloqueada',
            Subscription::STATUS_CANCELED => 'Cancelada',
        ];
    }

    /** Quais transições fazem sentido a partir do status atual. */
    private function availableTransitions(Farm $farm): array
    {
        $current = $farm->subscription?->status ?? Subscription::STATUS_TRIAL;
        $all = array_keys($this->statusOptions());
        return array_values(array_filter($all, fn ($s) => $s !== $current));
    }
}
