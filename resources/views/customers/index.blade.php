@extends('layouts.app')

@section('title', 'Clientes')

@section('content')
<div class="flex items-center justify-between mb-6 gap-4 flex-wrap">
    <div>
        <h1 class="text-2xl font-bold text-coffee-900">Clientes</h1>
        <p class="text-sm text-coffee-500 mt-0.5">Produtores parceiros da fazenda.</p>
    </div>
    @can('create', App\Models\Customer::class)
        <a href="{{ route('clientes.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-white bg-coffee-700 hover:bg-coffee-800 rounded-lg transition shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Novo cliente
        </a>
    @endcan
</div>

<form method="GET" class="bg-white rounded-xl border border-coffee-100 p-4 shadow-sm mb-4">
    <div class="flex gap-2">
        <input type="text" name="q" value="{{ $term }}" placeholder="Buscar por nome, telefone, CPF/CNPJ"
               class="flex-1 px-3.5 py-2 text-sm rounded-lg border border-coffee-200 focus:outline-none focus:ring-2 focus:ring-coffee-500 focus:border-coffee-500">
        <button type="submit" class="px-5 py-2 text-sm font-semibold text-white bg-coffee-700 hover:bg-coffee-800 rounded-lg transition">Buscar</button>
    </div>
</form>

<div class="bg-white rounded-xl border border-coffee-100 shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-coffee-50/50 text-coffee-600 text-xs uppercase tracking-wider">
            <tr>
                <th class="text-left px-6 py-3 font-semibold">Nome</th>
                <th class="text-left px-6 py-3 font-semibold">Telefone</th>
                <th class="text-left px-6 py-3 font-semibold">CPF/CNPJ</th>
                <th class="text-right px-6 py-3 font-semibold">Saldo (kg)</th>
                <th class="px-6 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-coffee-100">
            @forelse($customers as $c)
                <tr class="hover:bg-coffee-50/30 transition">
                    <td class="px-6 py-3.5">
                        <a href="{{ route('clientes.show', $c) }}" class="font-semibold text-coffee-800 hover:text-coffee-900 hover:underline">{{ $c->nome }}</a>
                    </td>
                    <td class="px-6 py-3.5 text-coffee-600">{{ $c->telefone ?? '—' }}</td>
                    <td class="px-6 py-3.5 text-coffee-600">{{ $c->cpf_cnpj ?? '—' }}</td>
                    <td class="px-6 py-3.5 text-right font-bold text-coffee-700">{{ number_format($c->saldo_cafe_kg, 3, ',', '.') }}</td>
                    <td class="px-6 py-3.5 text-right text-sm">
                        @can('update', $c)
                            <a href="{{ route('clientes.edit', $c) }}" class="text-coffee-600 hover:text-coffee-900 hover:underline">editar</a>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-6 py-12 text-center text-coffee-500">Nenhum cliente cadastrado.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $customers->links() }}</div>
@endsection
