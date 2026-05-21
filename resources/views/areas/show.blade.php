@extends('layouts.app')

@section('title', $area->nome)

@section('content')
<div class="flex items-start justify-between mb-6 gap-4 flex-wrap">
    <div>
        <p class="text-xs text-leaf-500 mb-1"><a href="{{ route('areas.index') }}" class="hover:underline">Áreas</a></p>
        <div class="flex items-center gap-2">
            <h1 class="text-2xl font-bold text-leaf-900">{{ $area->nome }}</h1>
            @if(! $area->ativo)
                <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-semibold bg-gray-100 text-gray-700">INATIVA</span>
            @endif
        </div>
        <p class="text-xs text-leaf-500 mt-1">Cadastrada em {{ $area->created_at->format('d/m/Y') }}</p>
    </div>
    <div class="flex gap-2">
        @if($area->hasLocation())
            <a href="https://www.google.com/maps?q={{ $area->latitude }},{{ $area->longitude }}" target="_blank" rel="noopener"
               class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-leaf-700 bg-white border border-leaf-200 hover:bg-leaf-50 rounded-lg transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Ver no mapa
            </a>
        @endif
        @can('update', $area)
            <a href="{{ route('areas.edit', $area) }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-leaf-700 hover:bg-leaf-800 rounded-lg transition shadow-sm">Editar</a>
        @endcan
    </div>
</div>

{{-- Filtro de período --}}
<div class="bg-white rounded-xl border border-leaf-100 shadow-sm p-4 mb-6">
    <form method="GET" class="flex flex-wrap items-end gap-3">
        <div>
            <label class="block text-[10px] uppercase tracking-wider text-leaf-500 font-semibold mb-1.5">Período</label>
            <select name="periodo" onchange="this.form.submit()"
                    class="px-3 py-2 text-sm rounded-lg border border-leaf-200 focus:border-leaf-500 focus:ring-2 focus:ring-leaf-500/15 outline-none transition bg-white">
                <option value="ano" {{ $periodo === 'ano' ? 'selected' : '' }}>Este ano</option>
                <option value="mes" {{ $periodo === 'mes' ? 'selected' : '' }}>Este mês</option>
                <option value="tudo" {{ $periodo === 'tudo' ? 'selected' : '' }}>Todo o histórico</option>
                <option value="custom" {{ $periodo === 'custom' ? 'selected' : '' }}>Período custom</option>
            </select>
        </div>
        @if($periodo === 'custom')
            <div>
                <label class="block text-[10px] uppercase tracking-wider text-leaf-500 font-semibold mb-1.5">De</label>
                <input type="date" name="de" value="{{ $de }}"
                       class="px-3 py-2 text-sm rounded-lg border border-leaf-200 focus:border-leaf-500 focus:ring-2 focus:ring-leaf-500/15 outline-none">
            </div>
            <div>
                <label class="block text-[10px] uppercase tracking-wider text-leaf-500 font-semibold mb-1.5">Até</label>
                <input type="date" name="ate" value="{{ $ate }}"
                       class="px-3 py-2 text-sm rounded-lg border border-leaf-200 focus:border-leaf-500 focus:ring-2 focus:ring-leaf-500/15 outline-none">
            </div>
            <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-leaf-700 hover:bg-leaf-800 rounded-lg transition">Aplicar</button>
        @endif
        <p class="text-xs text-leaf-500 ml-auto">Exibindo: <strong>{{ $stats['periodo_label'] }}</strong></p>
    </form>
</div>

{{-- Stats agregadas --}}
<div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl border border-leaf-100 shadow-sm p-5">
        <p class="text-xs uppercase tracking-wider text-leaf-500 font-semibold">Secagens</p>
        <p class="text-2xl font-bold text-leaf-900 mt-2">{{ $stats['qtd_secagens'] }}</p>
        <p class="text-xs text-leaf-500 mt-0.5">{{ $stats['qtd_secagens'] === 1 ? 'concluída' : 'concluídas' }} no período</p>
    </div>
    <div class="bg-white rounded-xl border border-leaf-100 shadow-sm p-5">
        <p class="text-xs uppercase tracking-wider text-leaf-500 font-semibold">Total recebido</p>
        <p class="text-2xl font-bold text-leaf-700 mt-2">{{ number_format($stats['total_recebido'], 3, ',', '.') }}</p>
        <p class="text-xs text-leaf-500 mt-0.5">kg que entraram na secagem</p>
    </div>
    <div class="bg-white rounded-xl border border-leaf-100 shadow-sm p-5">
        <p class="text-xs uppercase tracking-wider text-leaf-500 font-semibold">Total seco</p>
        <p class="text-2xl font-bold text-emerald-700 mt-2">{{ number_format($stats['total_seco'], 3, ',', '.') }}</p>
        <p class="text-xs text-leaf-500 mt-0.5">kg de café seco produzidos</p>
    </div>
    <div class="bg-white rounded-xl border border-leaf-100 shadow-sm p-5">
        <p class="text-xs uppercase tracking-wider text-leaf-500 font-semibold">Comissão paga</p>
        <p class="text-2xl font-bold text-amber-700 mt-2">{{ number_format($stats['total_comissao'], 3, ',', '.') }}</p>
        <p class="text-xs text-leaf-500 mt-0.5">kg em comissões nas secagens</p>
    </div>
</div>

<div class="grid lg:grid-cols-3 gap-6">
    {{-- Informações da área --}}
    <div class="lg:col-span-1 bg-white rounded-xl border border-leaf-100 shadow-sm p-6 space-y-4">
        @if($area->hasLocation())
            <div>
                <p class="text-xs uppercase tracking-wider text-leaf-500 font-semibold">Localização</p>
                <p class="text-sm text-leaf-900 mt-1">
                    {{ number_format($area->latitude, 7, '.', '') }}, {{ number_format($area->longitude, 7, '.', '') }}
                </p>
            </div>
        @endif
        @if($area->observacoes)
            <div>
                <p class="text-xs uppercase tracking-wider text-leaf-500 font-semibold">Observações</p>
                <p class="text-sm text-leaf-700 whitespace-pre-wrap mt-1">{{ $area->observacoes }}</p>
            </div>
        @endif
        @if(! $area->hasLocation() && ! $area->observacoes)
            <p class="text-sm text-leaf-500 italic">Sem informações extras cadastradas.</p>
        @endif
    </div>

    {{-- Últimas secagens da área --}}
    <div class="lg:col-span-2 bg-white rounded-xl border border-leaf-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-leaf-100 flex items-center justify-between">
            <h2 class="text-sm font-bold text-leaf-900 uppercase tracking-wider">Últimas secagens</h2>
            <span class="text-xs text-leaf-500">desta área</span>
        </div>
        <ul class="divide-y divide-leaf-100">
            @forelse($ultimasSecagens as $s)
                @php
                    $itemSums = $s->items;
                    $recebido = $itemSums->sum('quantidade_recebida_kg');
                    $seco = $itemSums->sum('quantidade_seca_kg');
                @endphp
                <li class="px-6 py-3">
                    <div class="flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <a href="{{ route('secagens.show', $s) }}" class="text-sm font-semibold text-leaf-900 hover:underline">Secagem #{{ $s->numero }}</a>
                            <p class="text-xs text-leaf-500">
                                {{ $s->data->format('d/m/Y') }} · {{ $s->dryer?->nome ?? '—' }} · {{ $itemSums->count() }} {{ $itemSums->count() === 1 ? 'item' : 'itens' }}
                            </p>
                        </div>
                        <div class="text-right whitespace-nowrap">
                            <p class="text-sm font-bold text-leaf-700">{{ number_format($recebido, 3, ',', '.') }} kg</p>
                            <p class="text-[10px] text-leaf-500">recebido · seco {{ number_format($seco, 3, ',', '.') }} kg</p>
                        </div>
                    </div>
                </li>
            @empty
                <li class="px-6 py-8 text-center text-sm text-leaf-500">Nenhuma secagem desta área no período.</li>
            @endforelse
        </ul>
    </div>
</div>
@endsection
