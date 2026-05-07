<?php

namespace App\Http\Controllers;

use App\Http\Requests\Customers\StoreCustomerRequest;
use App\Http\Requests\Customers\UpdateCustomerRequest;
use App\Models\Customer;
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

    public function store(StoreCustomerRequest $request): RedirectResponse
    {
        $customer = Customer::create($request->validated());

        return redirect()->route('clientes.index')
            ->with('flash', "Cliente {$customer->nome} criado.");
    }

    public function show(Customer $cliente): View
    {
        $this->ensureSameFarm($cliente);
        $this->authorize('view', $cliente);
        return view('customers.show', ['customer' => $cliente]);
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
            ->with('flash', "Cliente {$cliente->nome} atualizado.");
    }

    public function destroy(Customer $cliente): RedirectResponse
    {
        $this->ensureSameFarm($cliente);
        $this->authorize('delete', $cliente);
        $nome = $cliente->nome;
        $cliente->delete();

        return redirect()->route('clientes.index')
            ->with('flash', "Cliente {$nome} excluído.");
    }

    private function ensureSameFarm(Customer $customer): void
    {
        $user = auth()->user();
        if ($user && ! $user->isRoot() && $customer->farm_id !== $user->farm_id) {
            throw new AuthorizationException('Cliente fora da fazenda atual.');
        }
    }
}
