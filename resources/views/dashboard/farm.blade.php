@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-leaf-900">Dashboard</h1>
    <p class="text-sm text-leaf-500 mt-0.5">Visão geral da fazenda no mês corrente.</p>
</div>

@if($farm)
    <div class="bg-gradient-to-br from-leaf-700 to-leaf-800 text-white rounded-2xl p-6 mb-6 shadow-lg shadow-leaf-700/10">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <p class="text-xs uppercase tracking-wider text-leaf-200">Fazenda</p>
                <p class="text-2xl font-bold mt-1">{{ $farm->nome }}</p>
                @if($farm->cidade)
                    <p class="text-sm text-leaf-200 mt-0.5">{{ $farm->cidade }}{{ $farm->estado ? ' / '.$farm->estado : '' }}</p>
                @endif
            </div>
            <div class="text-right">
                @php $cls = match($farm->status){'active'=>'bg-emerald-500/20 text-emerald-100 border-emerald-300/30','partner'=>'bg-purple-500/20 text-purple-100 border-purple-300/30','blocked'=>'bg-rose-500/20 text-rose-100 border-rose-300/30','past_due'=>'bg-orange-500/20 text-orange-100 border-orange-300/30','canceled'=>'bg-gray-500/20 text-gray-100 border-gray-300/30', default=>'bg-amber-500/20 text-amber-100 border-amber-300/30'}; @endphp
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full border text-xs font-semibold {{ $cls }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                    {{ \App\Support\StatusLabels::farm($farm->status) }}
                </span>
                @if($farm->isOnTrial())
                    <p class="text-xs text-leaf-200 mt-2">Teste grátis até {{ $farm->trial_ends_at->format('d/m/Y') }}</p>
                @endif
            </div>
        </div>
    </div>
@endif

{{-- Cards estruturados de estoque: 2 cards (côco/seco) × 3 linhas (próprio/terceiros/total) clicáveis --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">
    <x-dashboard.saldo-card
        titulo="Café côco em estoque"
        icone-path="M12 2C8 6 5 10 5 14a7 7 0 0014 0c0-4-3-8-7-12z"
        tone="amber"
        :proprio="$metrics['saldoCocoFazendaKg']"
        :terceiros="$metrics['saldoCocoClientesKg']"
        :url-proprio="route('movimentacoes.fazenda.index', ['produto' => 'coco'])"
        :url-terceiros="route('saldos.clientes', ['produto' => 'coco'])"
        :url-total="route('saldos.geral', ['produto' => 'coco'])"
    />
    <x-dashboard.saldo-card
        titulo="Café seco em estoque"
        icone-path="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"
        tone="emerald"
        :proprio="$metrics['saldoSecoFazendaKg']"
        :terceiros="$metrics['saldoSecoClientesKg']"
        :url-proprio="route('movimentacoes.fazenda.index', ['produto' => 'seco'])"
        :url-terceiros="route('saldos.clientes', ['produto' => 'seco'])"
        :url-total="route('saldos.geral', ['produto' => 'seco'])"
    />
</div>

{{-- Cards secundários: clientes, despesas --}}
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
    <a href="{{ route('clientes.index') }}" class="bg-white rounded-xl border border-leaf-100 p-5 shadow-sm hover:shadow transition group">
        <div class="flex items-center justify-between mb-3">
            <p class="text-xs font-semibold uppercase tracking-wider text-leaf-500">Clientes</p>
            <div class="w-8 h-8 rounded-lg bg-leaf-100 text-leaf-700 flex items-center justify-center">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            </div>
        </div>
        <p class="text-2xl font-bold text-leaf-900 group-hover:text-leaf-700 transition">{{ number_format($metrics['clientes'], 0, ',', '.') }}</p>
    </a>
    <a href="{{ route('despesas.index') }}" class="bg-white rounded-xl border border-leaf-100 p-5 shadow-sm hover:shadow transition group">
        <div class="flex items-center justify-between mb-3">
            <p class="text-xs font-semibold uppercase tracking-wider text-leaf-500">Despesas do mês</p>
            <div class="w-8 h-8 rounded-lg bg-rose-100 text-rose-700 flex items-center justify-center">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
        <p class="text-2xl font-bold text-leaf-900 group-hover:text-leaf-700 transition">R$ {{ number_format($metrics['despesasMes'], 2, ',', '.') }}</p>
    </a>
</div>

<div class="bg-white rounded-xl border border-leaf-100 shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-leaf-100 flex items-center justify-between">
        <h2 class="text-sm font-bold text-leaf-900 uppercase tracking-wider">Top clientes por saldo</h2>
        <a href="{{ route('clientes.index') }}" class="text-xs font-semibold text-leaf-700 hover:underline">Todos os clientes →</a>
    </div>
    <div class="divide-y divide-leaf-100">
        @forelse($topClientes as $c)
            <a href="{{ route('clientes.show', $c) }}" class="px-6 py-3.5 flex items-center justify-between gap-3 hover:bg-leaf-50 transition">
                <span class="text-sm font-semibold text-leaf-900 truncate">{{ $c->nome }}</span>
                <span class="text-right">
                    <span class="block text-xs text-amber-700 font-semibold">{{ number_format($c->saldo_coco_kg, 2, ',', '.') }} kg café côco</span>
                    <span class="block text-xs text-emerald-700 font-semibold">{{ number_format($c->saldo_seco_kg, 2, ',', '.') }} kg café seco</span>
                </span>
            </a>
        @empty
            <div class="px-6 py-8 text-center text-sm text-leaf-500">Sem clientes.</div>
        @endforelse
    </div>
</div>
@endsection
