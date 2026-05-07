@extends('layouts.app')

@section('title', 'Cliente')

@section('content')
<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:14px; gap:12px;">
    <h1 class="page" style="margin:0;">{{ $customer->nome }}</h1>
    @can('update', $customer)
        <a href="{{ route('clientes.edit', $customer) }}" class="btn btn-primary" style="width:auto; padding:8px 14px;">Editar</a>
    @endcan
</div>

<div class="card" style="max-width:640px;">
    <p><strong>Telefone:</strong> {{ $customer->telefone ?? '—' }}</p>
    <p><strong>CPF/CNPJ:</strong> {{ $customer->cpf_cnpj ?? '—' }}</p>
    <p><strong>Saldo de café:</strong> {{ number_format($customer->saldo_cafe_kg, 3, ',', '.') }} kg</p>
    @if($customer->observacoes)
        <p><strong>Observações:</strong></p>
        <p style="white-space:pre-wrap;">{{ $customer->observacoes }}</p>
    @endif
</div>

<p style="margin-top:14px;"><a href="{{ route('clientes.index') }}">← voltar</a></p>
@endsection
