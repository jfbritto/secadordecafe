@extends('layouts.app')
@section('title', 'Assinatura')
@section('content')

<div class="max-w-3xl">
    <h1 class="text-2xl font-bold text-coffee-900 mb-6">Assinatura</h1>

    <div class="bg-white rounded-xl border border-coffee-100 shadow-sm p-6 mb-4">
        <p class="text-xs uppercase tracking-wider text-coffee-500 font-semibold">Fazenda</p>
        <p class="text-lg font-bold text-coffee-900 mt-1">{{ $farm->nome }}</p>
        @php $cls = match($farm->status){'active'=>'bg-emerald-100 text-emerald-700','blocked'=>'bg-rose-100 text-rose-700','past_due'=>'bg-amber-100 text-amber-700', default=>'bg-amber-100 text-amber-700'}; @endphp
        <span class="inline-block mt-2 px-2.5 py-1 rounded-full text-xs font-semibold {{ $cls }}">{{ strtoupper($farm->status) }}</span>
    </div>

    @if($subscription)
        <div class="bg-white rounded-xl border border-coffee-100 shadow-sm p-6">
            <h2 class="text-sm font-bold text-coffee-900 uppercase tracking-wider mb-4">Detalhes da assinatura</h2>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <dt class="text-coffee-500">Status</dt>
                    <dd class="text-coffee-900 font-semibold">{{ ucfirst(str_replace('_',' ', $subscription->status)) }}</dd>
                </div>
                @if($subscription->trial_ends_at)
                    <div class="flex justify-between">
                        <dt class="text-coffee-500">Trial termina em</dt>
                        <dd class="text-coffee-900 font-semibold">{{ $subscription->trial_ends_at->format('d/m/Y') }}</dd>
                    </div>
                @endif
                @if($subscription->current_period_end)
                    <div class="flex justify-between">
                        <dt class="text-coffee-500">Próximo vencimento</dt>
                        <dd class="text-coffee-900 font-semibold">{{ $subscription->current_period_end->format('d/m/Y') }}</dd>
                    </div>
                @endif
                @if($subscription->asaas_subscription_id)
                    <div class="flex justify-between">
                        <dt class="text-coffee-500">ID Asaas</dt>
                        <dd class="text-coffee-700 font-mono text-xs">{{ $subscription->asaas_subscription_id }}</dd>
                    </div>
                @endif
            </dl>
        </div>
    @else
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-5 text-amber-900">
            Esta fazenda ainda não possui assinatura registrada no Asaas.
        </div>
    @endif

    <p class="mt-4 text-xs text-coffee-500">
        Em produção, a integração com o Asaas é processada por webhook automaticamente.
        O acesso é bloqueado após {{ config('asaas.block_after_days') }} dias em atraso.
    </p>
</div>
@endsection
