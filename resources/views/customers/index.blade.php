@extends('layouts.app')

@section('title', 'Clientes')

@section('content')
<div class="flex items-center justify-between mb-6 gap-4 flex-wrap">
    <div>
        <h1 class="text-2xl font-bold text-leaf-900">Clientes</h1>
        <p class="text-sm text-leaf-500 mt-0.5">Produtores parceiros da fazenda.</p>
    </div>
    @can('create', App\Models\Customer::class)
        <a href="{{ route('clientes.create') }}" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-semibold text-white bg-leaf-700 hover:bg-leaf-800 rounded-lg transition shadow-sm w-full sm:w-auto">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Novo cliente
        </a>
    @endcan
</div>

<form method="GET" class="bg-white rounded-xl border border-leaf-100 p-4 shadow-sm mb-4">
    <div class="flex flex-col sm:flex-row gap-2">
        <input type="text" name="q" value="{{ $term }}" placeholder="Buscar por nome, telefone, CPF/CNPJ"
               class="flex-1 px-3.5 py-2.5 text-base sm:text-sm rounded-lg border border-leaf-200 focus:outline-none focus:ring-2 focus:ring-leaf-500 focus:border-leaf-500">
        <button type="submit" class="px-5 py-2.5 text-base sm:text-sm font-semibold text-white bg-leaf-700 hover:bg-leaf-800 rounded-lg transition">Buscar</button>
    </div>
</form>

<div class="bg-white rounded-xl border border-leaf-100 shadow-sm overflow-hidden">
    {{-- Mobile: cards (até md) --}}
    <ul class="md:hidden divide-y divide-leaf-100">
        @forelse($customers as $c)
            <li>
                <a href="{{ route('clientes.show', $c) }}" class="flex items-center gap-3 px-4 py-4 hover:bg-leaf-50/30 transition">
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-leaf-900 truncate">{{ $c->nome }}</p>
                        <p class="text-xs text-leaf-500 mt-0.5">
                            {{ $c->telefone ?? 'sem telefone' }}
                            @if($c->cpf_cnpj) · {{ $c->cpf_cnpj }} @endif
                        </p>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <p class="font-bold text-leaf-700 text-sm whitespace-nowrap">{{ number_format($c->saldo_cafe_kg, 3, ',', '.') }} kg</p>
                        <p class="text-[10px] text-leaf-400 uppercase tracking-wider mt-0.5">saldo</p>
                    </div>
                    <svg class="w-4 h-4 text-leaf-400 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </a>
            </li>
        @empty
            <li class="px-4 py-12 text-center text-leaf-500">Nenhum cliente cadastrado.</li>
        @endforelse
    </ul>

    {{-- Desktop: tabela (md+) --}}
    <table class="hidden md:table w-full text-sm">
        <thead class="bg-leaf-50/50 text-leaf-600 text-xs uppercase tracking-wider">
            <tr>
                <th class="text-left px-6 py-3 font-semibold">Nome</th>
                <th class="text-left px-6 py-3 font-semibold">Telefone</th>
                <th class="text-left px-6 py-3 font-semibold">CPF/CNPJ</th>
                <th class="text-right px-6 py-3 font-semibold">Saldo (kg)</th>
                <th class="px-6 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-leaf-100">
            @forelse($customers as $c)
                <tr class="hover:bg-leaf-50/30 transition">
                    <td class="px-6 py-3.5">
                        <a href="{{ route('clientes.show', $c) }}" class="font-semibold text-leaf-800 hover:text-leaf-900 hover:underline">{{ $c->nome }}</a>
                    </td>
                    <td class="px-6 py-3.5 text-leaf-600">{{ $c->telefone ?? '—' }}</td>
                    <td class="px-6 py-3.5 text-leaf-600">{{ $c->cpf_cnpj ?? '—' }}</td>
                    <td class="px-6 py-3.5 text-right font-bold text-leaf-700">{{ number_format($c->saldo_cafe_kg, 3, ',', '.') }}</td>
                    <td class="px-6 py-3.5 text-right text-sm">
                        <a href="{{ route('clientes.show', $c) }}"
                           class="inline-flex items-center gap-1 font-semibold text-leaf-700 hover:text-leaf-900 hover:underline">
                            Abrir
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-6 py-12 text-center text-leaf-500">Nenhum cliente cadastrado.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $customers->links() }}</div>
@endsection
