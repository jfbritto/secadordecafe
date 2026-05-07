@extends('layouts.app')
@section('title', 'Editar despesa')
@section('content')
<div class="max-w-3xl">
    <h1 class="text-2xl font-bold text-coffee-900 mb-6">Editar despesa</h1>
    <form method="POST" action="{{ route('despesas.update', $expense) }}" class="bg-white rounded-xl border border-coffee-100 shadow-sm p-6">
        @method('PUT')
        @include('expenses._form')
        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="px-5 py-2.5 text-sm font-semibold text-white bg-coffee-700 hover:bg-coffee-800 rounded-lg transition shadow-sm">Salvar</button>
            <a href="{{ route('despesas.index') }}" class="px-4 py-2.5 text-sm text-coffee-600 hover:text-coffee-900">Cancelar</a>
            @can('delete', $expense)
                <span class="flex-1"></span>
                <form method="POST" action="{{ route('despesas.destroy', $expense) }}" onsubmit="return confirm('Excluir despesa?');" class="inline">
                    @csrf @method('DELETE')
                    <button class="px-3 py-2 text-sm font-semibold text-rose-600 hover:bg-rose-50 rounded-lg transition">Excluir</button>
                </form>
            @endcan
        </div>
    </form>
</div>
@endsection
