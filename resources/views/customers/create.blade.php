@extends('layouts.app')

@section('title', 'Novo cliente')

@section('content')
<h1 class="page">Novo cliente</h1>

<form method="POST" action="{{ route('clientes.store') }}" class="card" style="max-width:640px;">
    @include('customers._form')

    <div style="display:flex; gap:8px; margin-top:8px;">
        <button type="submit" class="btn btn-primary" style="width:auto; padding:9px 18px;">Salvar</button>
        <a href="{{ route('clientes.index') }}" style="align-self:center;">cancelar</a>
    </div>
</form>
@endsection
