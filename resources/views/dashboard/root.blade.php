@extends('layouts.app')
@section('title', 'Dashboard ROOT')
@section('content')
<h1 class="page">Painel ROOT</h1>

<div class="grid">
    <div class="card metric"><h3>Total fazendas</h3><div class="value">{{ $totalFarms }}</div></div>
    <div class="card metric"><h3>Ativas</h3><div class="value" style="color:#166534;">{{ $farmsActive }}</div></div>
    <div class="card metric"><h3>Trial</h3><div class="value" style="color:#92400e;">{{ $farmsTrial }}</div></div>
    <div class="card metric"><h3>Pendentes</h3><div class="value" style="color:#a23b3b;">{{ $farmsPastDue }}</div></div>
    <div class="card metric"><h3>Bloqueadas</h3><div class="value" style="color:#991b1b;">{{ $farmsBlocked }}</div></div>
    <div class="card metric"><h3>Usuários totais</h3><div class="value">{{ $totalUsers }}</div></div>
    <div class="card metric"><h3>Novos cadastros (30d)</h3><div class="value">{{ $newFarms30d }}</div></div>
</div>

<div style="display:grid; gap:16px; grid-template-columns:1fr 1fr; margin-top:24px;">
    <div class="card">
        <strong>Assinaturas por status</strong>
        <ul style="margin-top:12px; padding-left:18px;">
            @foreach(['trial','active','past_due','canceled','blocked'] as $st)
                <li><strong>{{ ucfirst(str_replace('_',' ', $st)) }}:</strong> {{ $subscriptions[$st] ?? 0 }}</li>
            @endforeach
        </ul>
    </div>

    <div class="card" style="padding:0; overflow:hidden;">
        <div style="padding:16px 20px;"><strong>Fazendas recentes</strong></div>
        <table style="width:100%; border-collapse:collapse;">
            <thead style="background:#f9f4ec;">
                <tr>
                    <th style="text-align:left; padding:8px 14px;">Fazenda</th>
                    <th style="text-align:left; padding:8px 14px;">Status</th>
                    <th style="text-align:left; padding:8px 14px;">Criada em</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recentFarms as $f)
                    <tr style="border-top:1px solid #efe6d6;">
                        <td style="padding:8px 14px;">{{ $f->nome }}</td>
                        <td style="padding:8px 14px;">
                            @php $cls = match($f->status){'active'=>'badge-active','blocked'=>'badge-blocked', default => 'badge-trial'}; @endphp
                            <span class="badge {{ $cls }}">{{ strtoupper($f->status) }}</span>
                        </td>
                        <td style="padding:8px 14px;">{{ $f->created_at->format('d/m/Y') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
