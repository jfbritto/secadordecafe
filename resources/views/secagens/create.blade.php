@extends('layouts.app')
@section('title', 'Nova secagem')
@section('content')
<h1 class="page">Nova secagem</h1>

<form method="POST" action="{{ route('secagens.store') }}" class="card" style="max-width:560px;">
    @csrf
    <div class="field">
        <label>Data *</label>
        <input type="date" name="data" value="{{ old('data', now()->format('Y-m-d')) }}" required>
        @error('data')<small class="error">{{ $message }}</small>@enderror
    </div>
    <div class="field">
        <label>Secador *</label>
        <input type="text" name="secador" value="{{ old('secador') }}" required maxlength="80" placeholder="ex: Secador 1">
        @error('secador')<small class="error">{{ $message }}</small>@enderror
    </div>
    <div class="field">
        <label>Observações</label>
        <textarea name="observacoes" rows="3" style="width:100%; padding:10px 12px; border:1px solid #d6c9b6; border-radius:8px;">{{ old('observacoes') }}</textarea>
    </div>
    <div style="display:flex; gap:8px;">
        <button class="btn btn-primary" style="width:auto; padding:9px 18px;">Criar rascunho</button>
        <a href="{{ route('secagens.index') }}" style="align-self:center;">cancelar</a>
    </div>
</form>
@endsection
