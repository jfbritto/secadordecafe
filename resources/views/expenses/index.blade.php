@extends('layouts.app')

@section('title', 'Despesas')

@section('content')
<div class="flex items-center justify-between mb-6 gap-4 flex-wrap">
    <div>
        <h1 class="text-2xl font-bold text-coffee-900">Despesas</h1>
        <p class="text-sm text-coffee-500 mt-0.5">Lançamentos financeiros da fazenda.</p>
    </div>
    @can('create', App\Models\Expense::class)
        <a href="{{ route('despesas.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-white bg-coffee-700 hover:bg-coffee-800 rounded-lg transition shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Nova despesa
        </a>
    @endcan
</div>

<form method="GET" class="bg-white rounded-xl border border-coffee-100 shadow-sm p-4 mb-4">
    <div class="grid sm:grid-cols-4 gap-3 items-end">
        <div>
            <label class="block text-xs font-semibold text-coffee-700 mb-1.5">De</label>
            <input type="date" name="from" value="{{ $from }}"
                   class="w-full px-3 py-2 text-sm rounded-lg border border-coffee-200 focus:outline-none focus:ring-2 focus:ring-coffee-500">
        </div>
        <div>
            <label class="block text-xs font-semibold text-coffee-700 mb-1.5">Até</label>
            <input type="date" name="to" value="{{ $to }}"
                   class="w-full px-3 py-2 text-sm rounded-lg border border-coffee-200 focus:outline-none focus:ring-2 focus:ring-coffee-500">
        </div>
        <div>
            <label class="block text-xs font-semibold text-coffee-700 mb-1.5">Categoria</label>
            <select name="cat" class="w-full px-3 py-2 text-sm rounded-lg border border-coffee-200 focus:outline-none focus:ring-2 focus:ring-coffee-500">
                <option value="">— todas —</option>
                @foreach(\App\Models\Expense::CATEGORIAS as $k => $v)
                    <option value="{{ $k }}" @selected($cat === $k)>{{ $v }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="px-5 py-2 text-sm font-semibold text-white bg-coffee-700 hover:bg-coffee-800 rounded-lg transition">Filtrar</button>
    </div>
</form>

<div class="bg-white rounded-xl border border-coffee-100 shadow-sm p-6 mb-4">
    <div class="flex items-baseline justify-between mb-4">
        <h2 class="text-sm font-bold text-coffee-900 uppercase tracking-wider">Totais por categoria{{ $from || $to ? ' (no período)' : '' }}</h2>
        <div class="text-right">
            <p class="text-[10px] text-coffee-500 uppercase tracking-wider">Total geral</p>
            <p class="text-2xl font-bold text-coffee-700">R$ {{ number_format($totalGeral, 2, ',', '.') }}</p>
        </div>
    </div>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        @foreach(\App\Models\Expense::CATEGORIAS as $key => $label)
            @php $row = $totals[$key] ?? null; @endphp
            <div class="bg-coffee-50 rounded-lg p-3">
                <p class="text-[10px] text-coffee-500 uppercase tracking-wider">{{ $label }}</p>
                <p class="text-base font-bold text-coffee-800 mt-0.5">R$ {{ number_format($row->total ?? 0, 2, ',', '.') }}</p>
                <p class="text-[10px] text-coffee-500">{{ $row->qtd ?? 0 }} lançamento(s)</p>
            </div>
        @endforeach
    </div>
</div>

<div class="bg-white rounded-xl border border-coffee-100 shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-coffee-50/50 text-coffee-600 text-xs uppercase tracking-wider">
            <tr>
                <th class="text-left px-6 py-3 font-semibold">Data</th>
                <th class="text-left px-6 py-3 font-semibold">Descrição</th>
                <th class="text-left px-6 py-3 font-semibold">Categoria</th>
                <th class="text-right px-6 py-3 font-semibold">Total (R$)</th>
                <th class="px-6 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-coffee-100">
            @forelse($expenses as $e)
                <tr class="hover:bg-coffee-50/30 transition">
                    <td class="px-6 py-3 text-coffee-700">{{ $e->data->format('d/m/Y') }}</td>
                    <td class="px-6 py-3 text-coffee-900 font-medium">{{ $e->descricao }}</td>
                    <td class="px-6 py-3 text-coffee-600">{{ \App\Models\Expense::CATEGORIAS[$e->categoria] ?? $e->categoria }}</td>
                    <td class="px-6 py-3 text-right font-bold text-coffee-800">{{ number_format($e->valor_total, 2, ',', '.') }}</td>
                    <td class="px-6 py-3 text-right text-sm">
                        @can('update', $e)<a href="{{ route('despesas.edit', $e) }}" class="text-coffee-600 hover:text-coffee-900 hover:underline">editar</a>@endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-6 py-12 text-center text-coffee-500">Nenhuma despesa.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $expenses->links() }}</div>
@endsection
