@extends('layouts.app')

@section('title', $customer->nome)

@section('content')
<div class="flex items-start justify-between mb-6 gap-4 flex-wrap">
    <div>
        <p class="text-xs text-leaf-500 mb-1"><a href="{{ route('clientes.index') }}" class="hover:underline">Clientes</a></p>
        <h1 class="text-2xl font-bold text-leaf-900">{{ $customer->nome }}</h1>
        <p class="text-xs text-leaf-500 mt-1">Cliente desde {{ $customer->created_at->format('d/m/Y') }} · {{ $stats['qtd_movimentacoes'] }} {{ $stats['qtd_movimentacoes'] === 1 ? 'movimentação' : 'movimentações' }}</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('movimentacoes.index', ['tipo' => 'cliente', 'id' => $customer->id]) }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-leaf-700 bg-white border border-leaf-200 hover:bg-leaf-50 rounded-lg transition">
            Extrato
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        </a>
        @can('update', $customer)
            <a href="{{ route('clientes.edit', $customer) }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-leaf-700 hover:bg-leaf-800 rounded-lg transition shadow-sm">Editar</a>
        @endcan
    </div>
</div>

<div class="grid lg:grid-cols-3 gap-6 mb-6">
    <div class="lg:col-span-2 bg-white rounded-xl border border-leaf-100 shadow-sm p-6 space-y-4">
        <div class="grid sm:grid-cols-2 gap-5">
            <div>
                <p class="text-xs uppercase tracking-wider text-leaf-500 font-semibold">Telefone</p>
                <p class="text-leaf-900 mt-0.5">{{ $customer->telefone ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs uppercase tracking-wider text-leaf-500 font-semibold">CPF/CNPJ</p>
                <p class="text-leaf-900 mt-0.5">{{ $customer->cpf_cnpj ?? '—' }}</p>
            </div>
        </div>
        @if($customer->observacoes)
            <div class="pt-4 border-t border-leaf-100">
                <p class="text-xs uppercase tracking-wider text-leaf-500 font-semibold">Observações</p>
                <p class="text-leaf-700 whitespace-pre-wrap mt-0.5">{{ $customer->observacoes }}</p>
            </div>
        @endif
    </div>

    <div class="space-y-3">
        <div class="bg-gradient-to-br from-amber-600 to-amber-700 text-white rounded-xl shadow-lg p-5">
            <p class="text-xs uppercase tracking-wider text-amber-100 font-semibold">Café côco</p>
            <p class="text-3xl font-bold mt-1">{{ number_format($customer->saldo_coco_kg, 2, ',', '.') }} <span class="text-base font-normal text-amber-100">kg</span></p>
            <p class="text-xs text-amber-100 mt-0.5">{{ \App\Support\Sacos::formatSacos($customer->saldo_coco_kg) }}</p>
        </div>
        <div class="bg-gradient-to-br from-emerald-600 to-emerald-700 text-white rounded-xl shadow-lg p-5">
            <p class="text-xs uppercase tracking-wider text-emerald-100 font-semibold">Café seco</p>
            <p class="text-3xl font-bold mt-1">{{ number_format($customer->saldo_seco_kg, 2, ',', '.') }} <span class="text-base font-normal text-emerald-100">kg</span></p>
            <p class="text-xs text-emerald-100 mt-0.5">{{ \App\Support\Sacos::formatSacos($customer->saldo_seco_kg) }}</p>
        </div>
    </div>
</div>

{{-- Stats: histórico cumulativo --}}
<div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl border border-leaf-100 shadow-sm p-5">
        <p class="text-xs uppercase tracking-wider text-leaf-500 font-semibold">Entradas de côco</p>
        <p class="text-2xl font-bold text-amber-600 mt-2">{{ number_format($stats['total_entradas_coco'], 2, ',', '.') }}</p>
        <p class="text-xs text-leaf-500 mt-0.5">kg recebidos do cliente</p>
    </div>
    <div class="bg-white rounded-xl border border-leaf-100 shadow-sm p-5">
        <p class="text-xs uppercase tracking-wider text-leaf-500 font-semibold">Produção de seco</p>
        <p class="text-2xl font-bold text-emerald-600 mt-2">{{ number_format($stats['total_producao_seco'], 2, ',', '.') }}</p>
        <p class="text-xs text-leaf-500 mt-0.5">kg líquido após secagens</p>
    </div>
    <div class="bg-white rounded-xl border border-leaf-100 shadow-sm p-5">
        <p class="text-xs uppercase tracking-wider text-leaf-500 font-semibold">Saídas (seco)</p>
        <p class="text-2xl font-bold text-rose-600 mt-2">{{ number_format($stats['total_saidas_seco'], 2, ',', '.') }}</p>
        <p class="text-xs text-leaf-500 mt-0.5">kg retirados/vendidos</p>
    </div>
    <div class="bg-white rounded-xl border border-leaf-100 shadow-sm p-5">
        <p class="text-xs uppercase tracking-wider text-leaf-500 font-semibold">Secagens</p>
        <p class="text-2xl font-bold text-leaf-900 mt-2">{{ $stats['qtd_secagens'] }}</p>
        <p class="text-xs text-leaf-500 mt-0.5">{{ $stats['qtd_secagens'] === 1 ? 'vez que passou pelo secador' : 'vezes que passou pelo secador' }}</p>
    </div>
</div>

<div class="grid lg:grid-cols-2 gap-6">
    {{-- Últimas movimentações --}}
    <div class="bg-white rounded-xl border border-leaf-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-leaf-100 flex items-center justify-between">
            <h2 class="text-sm font-bold text-leaf-900 uppercase tracking-wider">Últimas movimentações</h2>
            <a href="{{ route('movimentacoes.index', ['tipo' => 'cliente', 'id' => $customer->id]) }}" class="text-xs font-semibold text-leaf-700 hover:underline inline-flex items-center gap-1">
                Ver tudo
                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>
        <ul class="divide-y divide-leaf-100">
            @forelse($ultimasMovimentacoes as $m)
                <li class="px-6 py-3 flex items-center gap-3">
                    @php
                        $cls = match($m->tipo) {
                            'entrada' => 'bg-emerald-100 text-emerald-700',
                            'secagem' => 'bg-leaf-100 text-leaf-700',
                            'ajuste'  => 'bg-amber-100 text-amber-700',
                            'saida'   => 'bg-rose-100 text-rose-700',
                            default   => 'bg-gray-100 text-gray-700',
                        };
                    @endphp
                    <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wider {{ $cls }}">{{ \App\Support\StatusLabels::movementTipo($m->tipo) }}</span>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-leaf-900 truncate">
                            @if($m->source instanceof \App\Models\Secagem)
                                <a href="{{ route('secagens.show', $m->source) }}" class="font-semibold hover:underline">Secagem #{{ $m->source->numero }}</a>
                            @else
                                <span class="font-semibold">{{ $m->observacao ?? '—' }}</span>
                            @endif
                        </p>
                        <p class="text-xs text-leaf-500">{{ $m->occurred_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <span class="text-sm font-bold whitespace-nowrap {{ $m->quantidade_kg < 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                        {{ ($m->quantidade_kg > 0 ? '+' : '') }}{{ number_format($m->quantidade_kg, 2, ',', '.') }} kg
                    </span>
                </li>
            @empty
                <li class="px-6 py-8 text-center text-sm text-leaf-500">Sem movimentações ainda.</li>
            @endforelse
        </ul>
    </div>

    {{-- Secagens recentes envolvendo este cliente --}}
    <div class="bg-white rounded-xl border border-leaf-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-leaf-100">
            <h2 class="text-sm font-bold text-leaf-900 uppercase tracking-wider">Secagens recentes</h2>
        </div>
        <ul class="divide-y divide-leaf-100">
            @forelse($ultimasSecagens as $s)
                @php $item = $s->items->first(); @endphp
                <li class="px-6 py-3">
                    <div class="flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <a href="{{ route('secagens.show', $s) }}" class="text-sm font-semibold text-leaf-900 hover:underline">Secagem #{{ $s->numero }}</a>
                            <p class="text-xs text-leaf-500">
                                {{ $s->data->format('d/m/Y') }} · {{ $s->dryer?->nome ?? '—' }}
                                @if($s->status === 'rascunho')
                                    <span class="ml-1 inline-block px-1.5 py-0.5 rounded text-[9px] font-semibold uppercase tracking-wider bg-amber-100 text-amber-700">Rascunho</span>
                                @endif
                            </p>
                        </div>
                        @if($item)
                            <div class="text-right whitespace-nowrap">
                                <p class="text-sm font-bold text-leaf-700">{{ number_format($item->quantidade_recebida_kg, 2, ',', '.') }} kg</p>
                                <p class="text-[10px] text-leaf-500">recebido · seco {{ number_format($item->quantidade_seca_kg, 2, ',', '.') }} kg</p>
                            </div>
                        @endif
                    </div>
                </li>
            @empty
                <li class="px-6 py-8 text-center text-sm text-leaf-500">Este cliente nunca passou por uma secagem.</li>
            @endforelse
        </ul>
    </div>
</div>
@endsection
