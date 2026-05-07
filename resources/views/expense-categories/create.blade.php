@extends('layouts.app')

@section('title', 'Nova categoria')

@section('content')
<div class="max-w-2xl">
    <h1 class="text-2xl font-bold text-coffee-900 mb-6">Nova categoria</h1>
    <form method="POST" action="{{ route('despesas.categorias.store') }}" class="bg-white rounded-xl border border-coffee-100 shadow-sm p-6">
        @include('expense-categories._form')
        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="px-5 py-2.5 text-sm font-semibold text-white bg-coffee-700 hover:bg-coffee-800 rounded-lg transition shadow-sm">Salvar</button>
            <a href="{{ route('despesas.categorias.index') }}" class="px-4 py-2.5 text-sm text-coffee-600 hover:text-coffee-900">Cancelar</a>
        </div>
    </form>
</div>
@endsection
