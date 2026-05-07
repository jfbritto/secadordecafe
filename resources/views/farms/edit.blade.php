@extends('layouts.app')

@section('title', 'Configurações da fazenda')

@section('content')
<h1 class="page">Configurações da fazenda</h1>

@if(session('flash'))
    <div class="card" style="margin-bottom:12px; background:#dcfce7;">{{ session('flash') }}</div>
@endif

<form method="POST" action="{{ route('fazenda.update') }}" class="card" style="max-width:640px;">
    @csrf @method('PUT')

    <div class="field">
        <label>Nome *</label>
        <input type="text" name="nome" value="{{ old('nome', $farm->nome) }}" required maxlength="150">
        @error('nome')<small class="error">{{ $message }}</small>@enderror
    </div>

    <div class="field">
        <label>Telefone</label>
        <input type="text" name="telefone" value="{{ old('telefone', $farm->telefone) }}" maxlength="30">
    </div>

    <div style="display:grid; gap:12px; grid-template-columns:2fr 1fr;">
        <div class="field">
            <label>Cidade</label>
            <input type="text" name="cidade" value="{{ old('cidade', $farm->cidade) }}" maxlength="120">
        </div>
        <div class="field">
            <label>UF</label>
            <input type="text" name="estado" value="{{ old('estado', $farm->estado) }}" maxlength="2" style="text-transform:uppercase;">
        </div>
    </div>

    <button type="submit" class="btn btn-primary" style="width:auto; padding:9px 18px;">Salvar</button>
</form>

<div class="card" style="max-width:640px; margin-top:16px;">
    <strong>Status atual:</strong> {{ strtoupper($farm->status) }}<br>
    <small>Slug: {{ $farm->slug }} · Criada em {{ $farm->created_at->format('d/m/Y') }}</small>
</div>
@endsection
