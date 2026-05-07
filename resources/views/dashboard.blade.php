@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<h1 class="page">Dashboard</h1>

@if($farm)
    <div class="card" style="margin-bottom:16px;">
        <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap;">
            <div>
                <div style="font-size:13px; color:var(--muted);">Fazenda</div>
                <div style="font-size:20px; font-weight:700;">{{ $farm->nome }}</div>
            </div>
            <div>
                @php $cls = match($farm->status){ 'active'=>'badge-active','blocked'=>'badge-blocked', default => 'badge-trial' }; @endphp
                <span class="badge {{ $cls }}">{{ strtoupper($farm->status) }}</span>
                @if($farm->isOnTrial())
                    <span class="badge badge-trial" style="margin-left:6px;">Trial até {{ $farm->trial_ends_at->format('d/m/Y') }}</span>
                @endif
            </div>
        </div>
    </div>
@endif

<div class="grid">
    <div class="card metric">
        <h3>Clientes</h3>
        <div class="value">{{ number_format($metrics['clientes'], 0, ',', '.') }}</div>
    </div>
    <div class="card metric">
        <h3>Saldo de café (kg)</h3>
        <div class="value">{{ number_format($metrics['saldoCafeKg'], 0, ',', '.') }}</div>
    </div>
    <div class="card metric">
        <h3>Secagens no mês</h3>
        <div class="value">{{ $metrics['secagensMes'] }}</div>
    </div>
    <div class="card metric">
        <h3>Despesas no mês (R$)</h3>
        <div class="value">{{ number_format($metrics['despesasMes'], 2, ',', '.') }}</div>
    </div>
</div>

<div class="card" style="margin-top:24px;">
    <strong>Próximos passos (Fase 1 entregue, fases pendentes):</strong>
    <ul>
        <li>Cadastros de clientes</li>
        <li>Movimentações + secagens</li>
        <li>Financeiro</li>
        <li>Cobrança Asaas</li>
    </ul>
</div>
@endsection
