@extends('layouts.app')

@section('title', 'Editar cliente')

@section('content')
<h1 class="page">Editar cliente</h1>

<form method="POST" action="{{ route('clientes.update', $customer) }}" class="card" style="max-width:640px;">
    @method('PUT')
    @include('customers._form')

    <div style="display:flex; gap:8px; margin-top:8px; align-items:center;">
        <button type="submit" class="btn btn-primary" style="width:auto; padding:9px 18px;">Salvar</button>
        <a href="{{ route('clientes.index') }}">cancelar</a>

        @can('delete', $customer)
            <span style="flex:1;"></span>
            <form method="POST" action="{{ route('clientes.destroy', $customer) }}"
                  onsubmit="return confirm('Excluir este cliente?');">
                @csrf @method('DELETE')
                <button type="submit" style="background:transparent; border:0; color:#a23b3b; cursor:pointer;">Excluir</button>
            </form>
        @endcan
    </div>
</form>
@endsection
