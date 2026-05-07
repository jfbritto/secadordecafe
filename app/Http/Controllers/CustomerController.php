<?php

namespace App\Http\Controllers;

use App\Actions\Movements\RegisterMovementAction;
use App\Http\Requests\Customers\StoreCustomerRequest;
use App\Http\Requests\Customers\UpdateCustomerRequest;
use App\Models\Customer;
use App\Models\Movement;
use App\Models\Secagem;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Customer::class);

        $term = $request->string('q')->toString() ?: null;
        $customers = Customer::query()
            ->search($term)
            ->orderBy('nome')
            ->paginate(15)
            ->withQueryString();

        return view('customers.index', compact('customers', 'term'));
    }

    public function create(): View
    {
        $this->authorize('create', Customer::class);
        return view('customers.create');
    }

    public function store(StoreCustomerRequest $request, RegisterMovementAction $register): RedirectResponse
    {
        $data = $request->validated();
        $saldoInicial = (float) ($data['saldo_cafe_kg'] ?? 0);
        $data['saldo_cafe_kg'] = 0; // saldo é controlado por movements

        $customer = Customer::create($data);

        if ($saldoInicial > 0) {
            $register->execute(
                customer: $customer,
                user: $request->user(),
                tipo: Movement::TIPO_ENTRADA,
                quantidade: $saldoInicial,
                observacao: 'Saldo inicial',
            );
        }

        return redirect()->route('clientes.index')
            ->with('flash', '<strong>' . e($customer->nome) . '</strong> cadastrado.');
    }

    public function show(Customer $cliente): View
    {
        $this->ensureSameFarm($cliente);
        $this->authorize('view', $cliente);

        // Totais por tipo de movimentação (tudo em 1 query)
        $totaisPorTipo = $cliente->movements()
            ->selectRaw('tipo, SUM(quantidade_kg) as total, COUNT(*) as cnt, COUNT(DISTINCT source_id) as cnt_source')
            ->groupBy('tipo')
            ->get()
            ->keyBy('tipo');

        $stats = [
            'total_entradas' => (float) ($totaisPorTipo[Movement::TIPO_ENTRADA]->total ?? 0),
            'total_secado' => abs((float) ($totaisPorTipo[Movement::TIPO_SECAGEM]->total ?? 0)),
            'total_saidas' => abs((float) ($totaisPorTipo[Movement::TIPO_SAIDA]->total ?? 0)),
            'qtd_secagens' => (int) ($totaisPorTipo[Movement::TIPO_SECAGEM]->cnt_source ?? 0),
            'qtd_movimentacoes' => (int) $totaisPorTipo->sum('cnt'),
        ];

        $ultimasMovimentacoes = $cliente->movements()
            ->with(['user', 'source'])
            ->limit(5)
            ->get();

        $ultimasSecagens = Secagem::query()
            ->whereHas('items', fn ($q) => $q->where('customer_id', $cliente->id))
            ->with(['dryer', 'items' => fn ($q) => $q->where('customer_id', $cliente->id)])
            ->orderByDesc('data')
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        return view('customers.show', [
            'customer' => $cliente,
            'stats' => $stats,
            'ultimasMovimentacoes' => $ultimasMovimentacoes,
            'ultimasSecagens' => $ultimasSecagens,
        ]);
    }

    public function edit(Customer $cliente): View
    {
        $this->ensureSameFarm($cliente);
        $this->authorize('update', $cliente);
        return view('customers.edit', ['customer' => $cliente]);
    }

    public function update(UpdateCustomerRequest $request, Customer $cliente): RedirectResponse
    {
        $this->ensureSameFarm($cliente);
        $cliente->update($request->validated());

        return redirect()->route('clientes.index')
            ->with('flash', '<strong>' . e($cliente->nome) . '</strong> atualizado.');
    }

    public function destroy(Customer $cliente): RedirectResponse
    {
        $this->ensureSameFarm($cliente);
        $this->authorize('delete', $cliente);
        $nome = $cliente->nome;
        $cliente->delete();

        return redirect()->route('clientes.index')
            ->with('flash', '<strong>' . e($nome) . '</strong> excluído.');
    }

    private function ensureSameFarm(Customer $customer): void
    {
        $user = auth()->user();
        if ($user && ! $user->isRoot() && $customer->farm_id !== $user->farm_id) {
            throw new AuthorizationException('Cliente fora da fazenda atual.');
        }
    }
}
