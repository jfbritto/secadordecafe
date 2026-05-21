@extends('layouts.app')
@section('title', 'Assinatura')
@section('content')

<div class="max-w-3xl">
    <h1 class="text-2xl font-bold text-leaf-900 mb-6">Assinatura</h1>

    <div class="bg-white rounded-xl border border-leaf-100 shadow-sm p-6 mb-4">
        <p class="text-xs uppercase tracking-wider text-leaf-500 font-semibold">Fazenda</p>
        <p class="text-lg font-bold text-leaf-900 mt-1">{{ $farm->nome }}</p>
        @php $cls = match($farm->status){'active'=>'bg-emerald-100 text-emerald-700','partner'=>'bg-purple-100 text-purple-700','blocked'=>'bg-rose-100 text-rose-700','past_due'=>'bg-orange-100 text-orange-700','canceled'=>'bg-gray-100 text-gray-700', default=>'bg-amber-100 text-amber-700'}; @endphp
        <span class="inline-block mt-2 px-2.5 py-1 rounded-full text-xs font-semibold {{ $cls }}">{{ \App\Support\StatusLabels::farm($farm->status) }}</span>
    </div>

    @if($subscription)
        <div class="bg-white rounded-xl border border-leaf-100 shadow-sm p-6">
            <h2 class="text-sm font-bold text-leaf-900 uppercase tracking-wider mb-4">Detalhes da assinatura</h2>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <dt class="text-leaf-500">Status</dt>
                    <dd class="text-leaf-900 font-semibold">{{ ucfirst(str_replace('_',' ', $subscription->status)) }}</dd>
                </div>
                @if($subscription->trial_ends_at)
                    <div class="flex justify-between">
                        <dt class="text-leaf-500">Período de teste termina em</dt>
                        <dd class="text-leaf-900 font-semibold">{{ $subscription->trial_ends_at->format('d/m/Y') }}</dd>
                    </div>
                @endif
                @if($subscription->current_period_end)
                    <div class="flex justify-between">
                        <dt class="text-leaf-500">Próximo vencimento</dt>
                        <dd class="text-leaf-900 font-semibold">{{ $subscription->current_period_end->format('d/m/Y') }}</dd>
                    </div>
                @endif
                @if($subscription->asaas_subscription_id)
                    <div class="flex justify-between">
                        <dt class="text-leaf-500">Código de cobrança</dt>
                        <dd class="text-leaf-700 font-mono text-xs">{{ $subscription->asaas_subscription_id }}</dd>
                    </div>
                @endif
            </dl>
        </div>
    @else
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-5 text-amber-900">
            Esta fazenda ainda não tem assinatura ativa de cobrança.
        </div>
    @endif

    <p class="mt-4 text-xs text-leaf-500">
        A cobrança é processada automaticamente.
        Se o pagamento atrasar por mais de {{ config('asaas.block_after_days') }} dias, o acesso é suspenso.
    </p>
</div>
@endsection
