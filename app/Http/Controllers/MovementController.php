<?php

namespace App\Http\Controllers;

use App\Actions\Movements\RegisterMovementAction;
use App\Http\Requests\Movements\StoreMovementRequest;
use App\Models\Customer;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MovementController extends Controller
{
    public function index(Customer $cliente): View
    {
        $this->ensureSameFarm($cliente);
        $this->authorize('view', $cliente);

        $movements = $cliente->movements()->with('user')->paginate(30);

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
            ->with('flash', 'Movimentação registrada. Novo saldo: <strong>' . number_format((float) $cliente->saldo_cafe_kg, 3, ',', '.') . ' kg</strong>.');
    }

    private function ensureSameFarm(Customer $customer): void
    {
        $user = auth()->user();
        if ($user && ! $user->isRoot() && $customer->farm_id !== $user->farm_id) {
            throw new AuthorizationException();
        }
    }
}
