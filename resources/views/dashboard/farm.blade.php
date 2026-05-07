@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
<h1 class="page">Dashboard</h1>

@if($farm)
    <div class="card" style="margin-bottom:16px;">
        <div style="display:flex; justify-content:space-between; flex-wrap:wrap; gap:12px;">
            <div>
                <div style="font-size:13px; color:var(--muted);">Fazenda</div>
                <div style="font-size:20px; font-weight:700;">{{ $farm->nome }}</div>
            </div>
            <div>
                @php $cls = match($farm->status){'active'=>'badge-active','blocked'=>'badge-blocked', default => 'badge-trial'}; @endphp
                <span class="badge {{ $cls }}">{{ strtoupper($farm->status) }}</span>
                @if($farm->isOnTrial())
                    <span class="badge badge-trial" style="margin-left:6px;">Trial até {{ $farm->trial_ends_at->format('d/m/Y') }}</span>
                @endif
            </div>
        </div>
    </div>
@endif

<div class="grid">
    <div class="card metric"><h3>Clientes</h3><div class="value">{{ number_format($metrics['clientes'], 0, ',', '.') }}</div></div>
    <div class="card metric"><h3>Saldo total (kg)</h3><div class="value">{{ number_format($metrics['saldoCafeKg'], 0, ',', '.') }}</div></div>
    <div class="card metric"><h3>Secagens no mês</h3><div class="value">{{ $metrics['secagensConcluidasMes'] }}<small style="font-size:14px; color:var(--muted);">/{{ $metrics['secagensMes'] }}</small></div></div>
    <div class="card metric"><h3>Kg secados no mês</h3><div class="value">{{ number_format($metrics['kgSecadosMes'], 0, ',', '.') }}</div></div>
    <div class="card metric"><h3>Despesas do mês</h3><div class="value">R$ {{ number_format($metrics['despesasMes'], 2, ',', '.') }}</div></div>
</div>

<div style="display:grid; gap:16px; grid-template-columns:1fr 1fr; margin-top:24px;">
    <div class="card" style="padding:0; overflow:hidden;">
        <div style="padding:16px 20px; border-bottom:1px solid #efe6d6;"><strong>Últimas movimentações</strong></div>
        @forelse($ultimasMovs as $m)
            <div style="padding:10px 20px; border-top:1px solid #efe6d6; display:flex; justify-content:space-between;">
                <div>
                    <div style="font-weight:600;">{{ $m->customer?->nome ?? '—' }}</div>
                    <small style="color:#7d6b58;">{{ ucfirst($m->tipo) }} · {{ $m->occurred_at->format('d/m H:i') }}</small>
                </div>
                <div style="font-weight:700; color:{{ $m->quantidade_kg < 0 ? '#a23b3b' : '#166534' }};">
                    {{ ($m->quantidade_kg > 0 ? '+' : '') }}{{ number_format($m->quantidade_kg, 3, ',', '.') }} kg
                </div>
            </div>
        @empty
            <div style="padding:20px; color:#7d6b58;">Nenhuma movimentação ainda.</div>
        @endforelse
    </div>

    <div class="card" style="padding:0; overflow:hidden;">
        <div style="padding:16px 20px; border-bottom:1px solid #efe6d6;"><strong>Top clientes por saldo</strong></div>
        @forelse($topClientes as $c)
            <div style="padding:10px 20px; border-top:1px solid #efe6d6; display:flex; justify-content:space-between;">
                <a href="{{ route('clientes.show', $c) }}" style="color:#2b2218; font-weight:600;">{{ $c->nome }}</a>
                <div style="font-weight:700; color:#5a3a22;">{{ number_format($c->saldo_cafe_kg, 3, ',', '.') }} kg</div>
            </div>
        @empty
            <div style="padding:20px; color:#7d6b58;">Sem clientes.</div>
        @endforelse
    </div>
</div>
@endsection
