@extends('layouts.app')

@section('title', 'Editar cliente')

@section('content')
<div class="max-w-2xl">
    <h1 class="text-2xl font-bold text-coffee-900 mb-6">Editar cliente</h1>

    <form method="POST" action="{{ route('clientes.update', $customer) }}" class="bg-white rounded-xl border border-coffee-100 shadow-sm p-6">
        @method('PUT')
        @include('customers._form')

        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="px-5 py-2.5 text-sm font-semibold text-white bg-coffee-700 hover:bg-coffee-800 rounded-lg transition shadow-sm">Salvar</button>
            <a href="{{ route('clientes.index') }}" class="text-sm text-coffee-600 hover:text-coffee-900">Cancelar</a>

            @can('delete', $customer)
                <span class="flex-1"></span>
                <form method="POST" action="{{ route('clientes.destroy', $customer) }}" onsubmit="return confirm('Excluir este cliente?');" class="inline">
                    @csrf @method('DELETE')
                    <button type="submit" class="px-3 py-2 text-sm font-semibold text-rose-600 hover:bg-rose-50 rounded-lg transition">Excluir</button>
                </form>
            @endcan
        </div>
    </form>
</div>
@endsection
