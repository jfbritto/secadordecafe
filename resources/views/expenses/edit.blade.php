@extends('layouts.app')
@section('title', 'Editar despesa')
@section('content')
<h1 class="page">Editar despesa</h1>
<form method="POST" action="{{ route('despesas.update', $expense) }}" class="card" style="max-width:760px;">
    @method('PUT')
    @include('expenses._form')
    <div style="display:flex; gap:8px; align-items:center;">
        <button class="btn btn-primary" style="width:auto; padding:9px 18px;">Salvar</button>
        <a href="{{ route('despesas.index') }}">cancelar</a>
        @can('delete', $expense)
            <span style="flex:1;"></span>
            <form method="POST" action="{{ route('despesas.destroy', $expense) }}" onsubmit="return confirm('Excluir despesa?');">
                @csrf @method('DELETE')
                <button style="background:transparent; border:0; color:#a23b3b; cursor:pointer;">Excluir</button>
            </form>
        @endcan
    </div>
</form>
@endsection
