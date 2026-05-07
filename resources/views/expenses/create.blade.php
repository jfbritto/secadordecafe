@extends('layouts.app')
@section('title', 'Nova despesa')
@section('content')
<h1 class="page">Nova despesa</h1>
<form method="POST" action="{{ route('despesas.store') }}" class="card" style="max-width:760px;">
    @include('expenses._form')
    <div style="display:flex; gap:8px;">
        <button class="btn btn-primary" style="width:auto; padding:9px 18px;">Salvar</button>
        <a href="{{ route('despesas.index') }}" style="align-self:center;">cancelar</a>
    </div>
</form>
@endsection
