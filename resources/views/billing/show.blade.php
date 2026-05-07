@extends('layouts.app')
@section('title', 'Assinatura')
@section('content')
<h1 class="page">Assinatura</h1>

<div class="card" style="max-width:640px; margin-bottom:14px;">
    <strong>Fazenda:</strong> {{ $farm->nome }}<br>
    <strong>Status da fazenda:</strong>
    @php $cls = match($farm->status){'active'=>'badge-active','blocked'=>'badge-blocked', default => 'badge-trial'}; @endphp
    <span class="badge {{ $cls }}">{{ strtoupper($farm->status) }}</span>
</div>

@if($subscription)
    <div class="card" style="max-width:640px;">
        <strong>Assinatura</strong><br>
        Status: {{ ucfirst(str_replace('_',' ',$subscription->status)) }}<br>
        @if($subscription->trial_ends_at)
            Trial termina em: {{ $subscription->trial_ends_at->format('d/m/Y') }}<br>
        @endif
        @if($subscription->current_period_end)
            Próximo vencimento: {{ $subscription->current_period_end->format('d/m/Y') }}<br>
        @endif
        @if($subscription->asaas_subscription_id)
            <small style="color:#7d6b58;">ID Asaas: {{ $subscription->asaas_subscription_id }}</small>
        @endif
    </div>
@else
    <div class="card" style="max-width:640px; background:#fef3c7;">
        Esta fazenda ainda não possui assinatura registrada no Asaas.
    </div>
@endif

<p style="margin-top:14px; color:#7d6b58; font-size:13px;">
    Em produção, a integração com o Asaas é processada por webhook automaticamente. O acesso é bloqueado após
    {{ config('asaas.block_after_days') }} dias em atraso.
</p>
@endsection
