@extends('layouts.app')

@section('title', 'Clientes')

@section('content')
<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:14px; gap:12px; flex-wrap:wrap;">
    <h1 class="page" style="margin:0;">Clientes</h1>
    @can('create', App\Models\Customer::class)
        <a href="{{ route('clientes.create') }}" class="btn btn-primary" style="width:auto; padding:8px 14px;">+ Novo cliente</a>
    @endcan
</div>

@if(session('flash'))
    <div class="card" style="margin-bottom:12px; background:#dcfce7;">{{ session('flash') }}</div>
@endif

<form method="GET" class="card" style="margin-bottom:12px;">
    <div style="display:flex; gap:8px;">
        <input type="text" name="q" value="{{ $term }}" placeholder="Buscar por nome, telefone, CPF/CNPJ" style="flex:1;">
        <button type="submit" class="btn btn-primary" style="width:auto; padding:8px 16px;">Buscar</button>
    </div>
</form>

<div class="card" style="padding:0; overflow:hidden;">
    <table style="width:100%; border-collapse:collapse;">
        <thead style="background:#f9f4ec;">
            <tr>
                <th style="text-align:left; padding:10px 14px;">Nome</th>
                <th style="text-align:left; padding:10px 14px;">Telefone</th>
                <th style="text-align:left; padding:10px 14px;">CPF/CNPJ</th>
                <th style="text-align:right; padding:10px 14px;">Saldo (kg)</th>
                <th style="padding:10px 14px;"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($customers as $c)
                <tr style="border-top:1px solid #efe6d6;">
                    <td style="padding:10px 14px;"><a href="{{ route('clientes.show', $c) }}">{{ $c->nome }}</a></td>
                    <td style="padding:10px 14px;">{{ $c->telefone ?? '—' }}</td>
                    <td style="padding:10px 14px;">{{ $c->cpf_cnpj ?? '—' }}</td>
                    <td style="padding:10px 14px; text-align:right;">{{ number_format($c->saldo_cafe_kg, 3, ',', '.') }}</td>
                    <td style="padding:10px 14px; text-align:right;">
                        @can('update', $c)
                            <a href="{{ route('clientes.edit', $c) }}">editar</a>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" style="padding:24px; text-align:center; color:#7d6b58;">Nenhum cliente cadastrado.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div style="margin-top:14px;">
    {{ $customers->links() }}
</div>
@endsection
