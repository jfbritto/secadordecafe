@extends('layouts.app')

@section('title', $customer->nome)

@section('content')
<div class="flex items-start justify-between mb-6 gap-4 flex-wrap">
    <div>
        <p class="text-xs text-coffee-500 mb-1"><a href="{{ route('clientes.index') }}" class="hover:underline">Clientes</a></p>
        <h1 class="text-2xl font-bold text-coffee-900">{{ $customer->nome }}</h1>
        <p class="text-xs text-coffee-500 mt-1">Cliente desde {{ $customer->created_at->format('d/m/Y') }} · {{ $stats['qtd_movimentacoes'] }} {{ $stats['qtd_movimentacoes'] === 1 ? 'movimentação' : 'movimentações' }}</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('clientes.movimentacoes.index', $customer) }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-coffee-700 bg-white border border-coffee-200 hover:bg-coffee-50 rounded-lg transition">
            Extrato
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        </a>
        @can('update', $customer)
            <a href="{{ route('clientes.edit', $customer) }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-coffee-700 hover:bg-coffee-800 rounded-lg transition shadow-sm">Editar</a>
        @endcan
    </div>
</div>

<div class="grid lg:grid-cols-3 gap-6 mb-6">
    <div class="lg:col-span-2 bg-white rounded-xl border border-coffee-100 shadow-sm p-6 space-y-4">
        <div class="grid sm:grid-cols-2 gap-5">
            <div>
                <p class="text-xs uppercase tracking-wider text-coffee-500 font-semibold">Telefone</p>
                <p class="text-coffee-900 mt-0.5">{{ $customer->telefone ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs uppercase tracking-wider text-coffee-500 font-semibold">CPF/CNPJ</p>
                <p class="text-coffee-900 mt-0.5">{{ $customer->cpf_cnpj ?? '—' }}</p>
            </div>
        </div>
        @if($customer->observacoes)
            <div class="pt-4 border-t border-coffee-100">
                <p class="text-xs uppercase tracking-wider text-coffee-500 font-semibold">Observações</p>
                <p class="text-coffee-700 whitespace-pre-wrap mt-0.5">{{ $customer->observacoes }}</p>
            </div>
        @endif
    </div>

    <div class="bg-gradient-to-br from-coffee-700 to-coffee-800 text-white rounded-xl shadow-lg shadow-coffee-700/10 p-6">
        <p class="text-xs uppercase tracking-wider text-coffee-200 font-semibold">Saldo de café</p>
        <p class="text-4xl font-bold mt-2">{{ number_format($customer->saldo_cafe_kg, 3, ',', '.') }}</p>
        <p class="text-sm text-coffee-200 mt-1">kg em estoque</p>
    </div>
</div>

{{-- Stats: histórico cumulativo --}}
<div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl border border-coffee-100 shadow-sm p-5">
        <p class="text-xs uppercase tracking-wider text-coffee-500 font-semibold">Total recebido</p>
        <p class="text-2xl font-bold text-emerald-600 mt-2">{{ number_format($stats['total_entradas'], 3, ',', '.') }}</p>
        <p class="text-xs text-coffee-500 mt-0.5">kg de café que entraram</p>
    </div>
    <div class="bg-white rounded-xl border border-coffee-100 shadow-sm p-5">
        <p class="text-xs uppercase tracking-wider text-coffee-500 font-semibold">Total secado</p>
        <p class="text-2xl font-bold text-coffee-700 mt-2">{{ number_format($stats['total_secado'], 3, ',', '.') }}</p>
        <p class="text-xs text-coffee-500 mt-0.5">kg debitados em secagens</p>
    </div>
    <div class="bg-white rounded-xl border border-coffee-100 shadow-sm p-5">
        <p class="text-xs uppercase tracking-wider text-coffee-500 font-semibold">Total saídas</p>
        <p class="text-2xl font-bold text-rose-600 mt-2">{{ number_format($stats['total_saidas'], 3, ',', '.') }}</p>
        <p class="text-xs text-coffee-500 mt-0.5">kg retirados sem secagem</p>
    </div>
    <div class="bg-white rounded-xl border border-coffee-100 shadow-sm p-5">
        <p class="text-xs uppercase tracking-wider text-coffee-500 font-semibold">Secagens</p>
        <p class="text-2xl font-bold text-coffee-900 mt-2">{{ $stats['qtd_secagens'] }}</p>
        <p class="text-xs text-coffee-500 mt-0.5">{{ $stats['qtd_secagens'] === 1 ? 'vez que passou pelo secador' : 'vezes que passou pelo secador' }}</p>
    </div>
</div>

<div class="grid lg:grid-cols-2 gap-6">
    {{-- Últimas movimentações --}}
    <div class="bg-white rounded-xl border border-coffee-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-coffee-100 flex items-center justify-between">
            <h2 class="text-sm font-bold text-coffee-900 uppercase tracking-wider">Últimas movimentações</h2>
            <a href="{{ route('clientes.movimentacoes.index', $customer) }}" class="text-xs font-semibold text-coffee-700 hover:underline inline-flex items-center gap-1">
                Ver tudo
                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>
        <ul class="divide-y divide-coffee-100">
            @forelse($ultimasMovimentacoes as $m)
                <li class="px-6 py-3 flex items-center gap-3">
                    @php
                        $cls = match($m->tipo) {
                            'entrada' => 'bg-emerald-100 text-emerald-700',
                            'secagem' => 'bg-coffee-100 text-coffee-700',
                            'ajuste'  => 'bg-amber-100 text-amber-700',
                            'saida'   => 'bg-rose-100 text-rose-700',
                            default   => 'bg-gray-100 text-gray-700',
                        };
                    @endphp
                    <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wider {{ $cls }}">{{ $m->tipo }}</span>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-coffee-900 truncate">
                            @if($m->source instanceof \App\Models\Secagem)
                                <a href="{{ route('secagens.show', $m->source) }}" class="font-semibold hover:underline">Secagem #{{ $m->source->numero }}</a>
                            @else
                                <span class="font-semibold">{{ $m->observacao ?? '—' }}</span>
                            @endif
                        </p>
                        <p class="text-xs text-coffee-500">{{ $m->occurred_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <span class="text-sm font-bold whitespace-nowrap {{ $m->quantidade_kg < 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                        {{ ($m->quantidade_kg > 0 ? '+' : '') }}{{ number_format($m->quantidade_kg, 3, ',', '.') }} kg
                    </span>
                </li>
            @empty
                <li class="px-6 py-8 text-center text-sm text-coffee-500">Sem movimentações ainda.</li>
            @endforelse
        </ul>
    </div>

    {{-- Secagens recentes envolvendo este cliente --}}
    <div class="bg-white rounded-xl border border-coffee-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-coffee-100">
            <h2 class="text-sm font-bold text-coffee-900 uppercase tracking-wider">Secagens recentes</h2>
        </div>
        <ul class="divide-y divide-coffee-100">
            @forelse($ultimasSecagens as $s)
                @php $item = $s->items->first(); @endphp
                <li class="px-6 py-3">
                    <div class="flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <a href="{{ route('secagens.show', $s) }}" class="text-sm font-semibold text-coffee-900 hover:underline">Secagem #{{ $s->numero }}</a>
                            <p class="text-xs text-coffee-500">
                                {{ $s->data->format('d/m/Y') }} · {{ $s->dryer?->nome ?? '—' }}
                                @if($s->status === 'rascunho')
                                    <span class="ml-1 inline-block px-1.5 py-0.5 rounded text-[9px] font-semibold uppercase tracking-wider bg-amber-100 text-amber-700">Rascunho</span>
                                @endif
                            </p>
                        </div>
                        @if($item)
                            <div class="text-right whitespace-nowrap">
                                <p class="text-sm font-bold text-coffee-700">{{ number_format($item->quantidade_recebida_kg, 3, ',', '.') }} kg</p>
                                <p class="text-[10px] text-coffee-500">recebido · seco {{ number_format($item->quantidade_seca_kg, 3, ',', '.') }} kg</p>
                            </div>
                        @endif
                    </div>
                </li>
            @empty
                <li class="px-6 py-8 text-center text-sm text-coffee-500">Este cliente nunca passou por uma secagem.</li>
            @endforelse
        </ul>
    </div>
</div>
@endsection
