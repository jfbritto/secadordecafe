@extends('layouts.app')

@section('title', $customer->nome)

@section('content')
<div class="flex items-center justify-between mb-6 gap-4 flex-wrap">
    <div>
        <p class="text-xs text-coffee-500 mb-1"><a href="{{ route('clientes.index') }}" class="hover:underline">Clientes</a></p>
        <h1 class="text-2xl font-bold text-coffee-900">{{ $customer->nome }}</h1>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('clientes.movimentacoes.index', $customer) }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-coffee-700 bg-white border border-coffee-200 hover:bg-coffee-50 rounded-lg transition">
            Extrato
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        </a>
        @can('update', $customer)
            <a href="{{ route('clientes.edit', $customer) }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-coffee-700 hover:bg-coffee-800 rounded-lg transition shadow-sm">Editar</a>
        @endcan
    </div>
</div>

<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 bg-white rounded-xl border border-coffee-100 shadow-sm p-6 space-y-4">
        <div>
            <p class="text-xs uppercase tracking-wider text-coffee-500 font-semibold">Telefone</p>
            <p class="text-coffee-900">{{ $customer->telefone ?? '—' }}</p>
        </div>
        <div>
            <p class="text-xs uppercase tracking-wider text-coffee-500 font-semibold">CPF/CNPJ</p>
            <p class="text-coffee-900">{{ $customer->cpf_cnpj ?? '—' }}</p>
        </div>
        @if($customer->observacoes)
            <div>
                <p class="text-xs uppercase tracking-wider text-coffee-500 font-semibold">Observações</p>
                <p class="text-coffee-700 whitespace-pre-wrap">{{ $customer->observacoes }}</p>
            </div>
        @endif
    </div>

    <div class="bg-gradient-to-br from-coffee-700 to-coffee-800 text-white rounded-xl shadow-lg shadow-coffee-700/10 p-6">
        <p class="text-xs uppercase tracking-wider text-coffee-200 font-semibold">Saldo de café</p>
        <p class="text-4xl font-bold mt-2">{{ number_format($customer->saldo_cafe_kg, 3, ',', '.') }}</p>
        <p class="text-sm text-coffee-200 mt-1">kg em estoque</p>
    </div>
</div>
@endsection
