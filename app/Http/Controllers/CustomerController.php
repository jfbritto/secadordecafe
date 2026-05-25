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
        $saldoInicialCoco = (float) ($data['saldo_coco_kg'] ?? 0);
        $saldoInicialSeco = (float) ($data['saldo_seco_kg'] ?? 0);
        $data['saldo_coco_kg'] = 0;
        $data['saldo_seco_kg'] = 0;

        $customer = Customer::create($data);

        if ($saldoInicialCoco > 0) {
            $register->execute(
                owner: $customer,
                user: $request->user(),
                tipo: Movement::TIPO_ENTRADA,
                produto: Movement::PRODUTO_COCO,
                quantidade: $saldoInicialCoco,
                observacao: 'Saldo inicial de côco',
            );
        }

        if ($saldoInicialSeco > 0) {
            $register->execute(
                owner: $customer,
                user: $request->user(),
                tipo: Movement::TIPO_ENTRADA,
                produto: Movement::PRODUTO_SECO,
                quantidade: $saldoInicialSeco,
                observacao: 'Saldo inicial de seco',
            );
        }

        return redirect()->route('clientes.index')
            ->with('flash', '<strong>' . e($customer->nome) . '</strong> cadastrado.');
    }

    public function show(Customer $cliente): View
    {
        $this->ensureSameFarm($cliente);
        $this->authorize('view', $cliente);

        // Stats por tipo + produto (1 query)
        $stats = $cliente->movements()
            ->selectRaw('tipo, produto, SUM(quantidade_kg) as total, COUNT(*) as cnt, COUNT(DISTINCT source_id) as cnt_source')
            ->groupBy('tipo', 'produto')
            ->get();

        $get = fn (string $tipo, string $produto) =>
            (float) ($stats->firstWhere(fn ($r) => $r->tipo === $tipo && $r->produto === $produto)->total ?? 0);
        $cnt = fn (string $tipo, string $produto, string $col = 'cnt') =>
            (int) ($stats->firstWhere(fn ($r) => $r->tipo === $tipo && $r->produto === $produto)->{$col} ?? 0);

        $stats = [
            'total_entradas_coco' => $get(Movement::TIPO_ENTRADA, 'coco'),
            'total_secado_coco' => abs($get(Movement::TIPO_SECAGEM, 'coco')),
            'total_producao_seco' => $get(Movement::TIPO_PRODUCAO, 'seco'),
            'total_saidas_seco' => abs($get(Movement::TIPO_SAIDA, 'seco')),
            'total_saidas_coco' => abs($get(Movement::TIPO_SAIDA, 'coco')),
            'qtd_secagens' => $cnt(Movement::TIPO_SECAGEM, 'coco', 'cnt_source'),
            'qtd_movimentacoes' => $stats->sum('cnt'),
        ];

        $ultimasMovimentacoes = $cliente->movements()
            ->with(['user', 'source'])
            ->latest('occurred_at')
            ->limit(5)
            ->get();

        $ultimasSecagens = Secagem::query()
            ->whereHas('items', fn ($q) =>
                $q->where('origin_type', Customer::class)->where('origin_id', $cliente->id))
            ->with(['dryer', 'items' => fn ($q) =>
                $q->where('origin_type', Customer::class)->where('origin_id', $cliente->id)])
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
