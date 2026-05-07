@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-coffee-900">Dashboard</h1>
    <p class="text-sm text-coffee-500 mt-0.5">Visão geral da fazenda no mês corrente.</p>
</div>

@if($farm)
    <div class="bg-gradient-to-br from-coffee-700 to-coffee-800 text-white rounded-2xl p-6 mb-6 shadow-lg shadow-coffee-700/10">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <p class="text-xs uppercase tracking-wider text-coffee-200">Fazenda</p>
                <p class="text-2xl font-bold mt-1">{{ $farm->nome }}</p>
                @if($farm->cidade)
                    <p class="text-sm text-coffee-200 mt-0.5">{{ $farm->cidade }}{{ $farm->estado ? ' / '.$farm->estado : '' }}</p>
                @endif
            </div>
            <div class="text-right">
                @php $cls = match($farm->status){'active'=>'bg-emerald-500/20 text-emerald-100 border-emerald-300/30','blocked'=>'bg-rose-500/20 text-rose-100 border-rose-300/30','past_due'=>'bg-amber-500/20 text-amber-100 border-amber-300/30', default=>'bg-amber-500/20 text-amber-100 border-amber-300/30'}; @endphp
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full border text-xs font-semibold {{ $cls }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                    {{ strtoupper($farm->status) }}
                </span>
                @if($farm->isOnTrial())
                    <p class="text-xs text-coffee-200 mt-2">Trial até {{ $farm->trial_ends_at->format('d/m/Y') }}</p>
                @endif
            </div>
        </div>
    </div>
@endif

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
    @php
        $metricCards = [
            ['label'=>'Clientes', 'value'=>number_format($metrics['clientes'], 0, ',', '.'), 'icon'=>'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z', 'tone'=>'coffee'],
            ['label'=>'Saldo total (kg)', 'value'=>number_format($metrics['saldoCafeKg'], 0, ',', '.'), 'icon'=>'M3 8h14v9a4 4 0 01-4 4H7a4 4 0 01-4-4V8zM7 4l1 2M11 4l1 2M15 4l1 2', 'tone'=>'amber'],
            ['label'=>'Secagens no mês', 'value'=>$metrics['secagensConcluidasMes'].'/'.$metrics['secagensMes'], 'icon'=>'M5 8h14M5 12h14M5 16h14M4 4h16a1 1 0 011 1v14a1 1 0 01-1 1H4a1 1 0 01-1-1V5a1 1 0 011-1z', 'tone'=>'emerald'],
            ['label'=>'Kg secados no mês', 'value'=>number_format($metrics['kgSecadosMes'], 0, ',', '.'), 'icon'=>'M13 17l5-5-5-5M6 17l5-5-5-5', 'tone'=>'sky'],
            ['label'=>'Despesas do mês', 'value'=>'R$ '.number_format($metrics['despesasMes'], 2, ',', '.'), 'icon'=>'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'tone'=>'rose'],
        ];
        $tones = [
            'coffee'=>'bg-coffee-100 text-coffee-700',
            'amber'=>'bg-amber-100 text-amber-700',
            'emerald'=>'bg-emerald-100 text-emerald-700',
            'sky'=>'bg-sky-100 text-sky-700',
            'rose'=>'bg-rose-100 text-rose-700',
        ];
    @endphp
    @foreach($metricCards as $m)
        <div class="bg-white rounded-xl border border-coffee-100 p-5 shadow-sm hover:shadow transition">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs font-semibold uppercase tracking-wider text-coffee-500">{{ $m['label'] }}</p>
                <div class="w-8 h-8 rounded-lg {{ $tones[$m['tone']] }} flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $m['icon'] }}"/></svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-coffee-900">{{ $m['value'] }}</p>
        </div>
    @endforeach
</div>

<div class="grid lg:grid-cols-2 gap-6">
    <div class="bg-white rounded-xl border border-coffee-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-coffee-100">
            <h2 class="text-sm font-bold text-coffee-900 uppercase tracking-wider">Últimas movimentações</h2>
        </div>
        <div class="divide-y divide-coffee-100">
            @forelse($ultimasMovs as $m)
                <div class="px-6 py-3.5 flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-coffee-900 truncate">{{ $m->customer?->nome ?? '—' }}</p>
                        <p class="text-xs text-coffee-500">{{ ucfirst($m->tipo) }} · {{ $m->occurred_at->format('d/m H:i') }}{{ $m->user ? ' · '.$m->user->name : '' }}</p>
                    </div>
                    <span class="text-sm font-bold {{ $m->quantidade_kg < 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                        {{ ($m->quantidade_kg > 0 ? '+' : '') }}{{ number_format($m->quantidade_kg, 3, ',', '.') }} kg
                    </span>
                </div>
            @empty
                <div class="px-6 py-8 text-center text-sm text-coffee-500">Nenhuma movimentação ainda.</div>
            @endforelse
        </div>
    </div>

    <div class="bg-white rounded-xl border border-coffee-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-coffee-100">
            <h2 class="text-sm font-bold text-coffee-900 uppercase tracking-wider">Top clientes por saldo</h2>
        </div>
        <div class="divide-y divide-coffee-100">
            @forelse($topClientes as $c)
                <a href="{{ route('clientes.show', $c) }}" class="px-6 py-3.5 flex items-center justify-between gap-3 hover:bg-coffee-50 transition">
                    <span class="text-sm font-semibold text-coffee-900 truncate">{{ $c->nome }}</span>
                    <span class="text-sm font-bold text-coffee-700">{{ number_format($c->saldo_cafe_kg, 3, ',', '.') }} kg</span>
                </a>
            @empty
                <div class="px-6 py-8 text-center text-sm text-coffee-500">Sem clientes.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
