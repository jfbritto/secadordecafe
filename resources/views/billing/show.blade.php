@extends('layouts.app')
@section('title', 'Plano')
@section('content')

<div class="max-w-3xl">
    <h1 class="text-2xl font-bold text-leaf-900 mb-6">Plano</h1>

    <div class="bg-white rounded-xl border border-leaf-100 shadow-sm p-6 mb-4">
        <p class="text-xs uppercase tracking-wider text-leaf-500 font-semibold">Fazenda</p>
        <p class="text-lg font-bold text-leaf-900 mt-1">{{ $farm->nome }}</p>
        @php $cls = match($farm->status){'active'=>'bg-emerald-100 text-emerald-700','partner'=>'bg-purple-100 text-purple-700','blocked'=>'bg-rose-100 text-rose-700','past_due'=>'bg-orange-100 text-orange-700','canceled'=>'bg-gray-100 text-gray-700', default=>'bg-amber-100 text-amber-700'}; @endphp
        <span class="inline-block mt-2 px-2.5 py-1 rounded-full text-xs font-semibold {{ $cls }}">{{ \App\Support\StatusLabels::farm($farm->status) }}</span>
    </div>

    <div class="bg-purple-50 border border-purple-200 rounded-xl p-6 text-purple-900">
        <h2 class="text-base font-bold mb-2">Você está no plano Parceiro</h2>
        <p class="text-sm leading-relaxed">
            Acesso completo a todas as funções da Roça Nossa, sem cobrança e sem expiração.
            Estamos refinando o sistema com os primeiros produtores e, quando definirmos a estrutura
            de planos, você será avisado por e-mail com antecedência.
        </p>
    </div>
</div>
@endsection
