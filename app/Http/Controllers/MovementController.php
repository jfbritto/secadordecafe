<?php

namespace App\Http\Controllers;

use App\Actions\Movements\RegisterMovementAction;
use App\Http\Requests\Movements\StoreMovementRequest;
use App\Models\Customer;
use App\Models\Movement;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MovementController extends Controller
{
    public function index(Customer $cliente): View
    {
        $this->ensureSameFarm($cliente);
        $this->authorize('view', $cliente);

        $movements = $cliente->movements()->with(['user', 'source'])->paginate(30);

        // Running balance: saldo após cada movimentação, calculado a partir do cumulativo
        // anterior ao mais antigo da página (1 query SUM extra, escala bem).
        if ($movements->isNotEmpty()) {
            $oldestOnPage = $movements->last();
            $somaAntes = (float) Movement::query()
                ->where('customer_id', $cliente->id)
                ->where(function ($q) use ($oldestOnPage) {
                    $q->where('occurred_at', '<', $oldestOnPage->occurred_at)
                      ->orWhere(function ($q2) use ($oldestOnPage) {
                          $q2->where('occurred_at', $oldestOnPage->occurred_at)
                             ->where('id', '<', $oldestOnPage->id);
                      });
                })
                ->sum('quantidade_kg');

            $running = $somaAntes;
            $sortedAsc = $movements->getCollection()
                ->sortBy(fn ($m) => sprintf('%s-%020d', $m->occurred_at->format('YmdHis'), $m->id))
                ->values();

            foreach ($sortedAsc as $m) {
                $running += (float) $m->quantidade_kg;
                $m->saldo_apos = $running;
            }
        }

        return view('movements.index', [
            'customer' => $cliente,
            'movements' => $movements,
        ]);
    }

    public function store(StoreMovementRequest $request, Customer $cliente, RegisterMovementAction $action): RedirectResponse
    {
        $this->ensureSameFarm($cliente);

        $action->execute(
            customer: $cliente,
            user: $request->user(),
            tipo: $request->validated('tipo'),
            quantidade: (float) $request->validated('quantidade'),
            observacao: $request->validated('observacao'),
            direcao: $request->validated('direcao'),
            occurredAt: $request->validated('occurred_at') ? new \DateTime($request->validated('occurred_at')) : null,
        );

        $cliente->refresh();
        return redirect()->route('clientes.movimentacoes.index', $cliente)
            ->with('flash', 'Movimentação registrada. Novo saldo: <strong>' . number_format((float) $cliente->saldo_cafe_kg, 2, ',', '.') . ' kg</strong>.');
    }

    private function ensureSameFarm(Customer $customer): void
    {
        $user = auth()->user();
        if ($user && ! $user->isRoot() && $customer->farm_id !== $user->farm_id) {
            throw new AuthorizationException();
        }
    }
}
