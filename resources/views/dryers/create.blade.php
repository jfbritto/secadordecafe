@extends('layouts.app')

@section('title', 'Novo secador')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <p class="text-xs text-coffee-500 mb-1">
            <a href="{{ route('secadores.index') }}" class="hover:underline">Secadores</a> · <span class="text-coffee-700">Novo</span>
        </p>
        <h1 class="text-2xl font-bold text-coffee-900">Novo secador</h1>
        <p class="text-sm text-coffee-500 mt-1">Cadastre os equipamentos da fazenda. Você poderá selecioná-los ao criar uma secagem.</p>
    </div>

    <form method="POST" action="{{ route('secadores.store') }}" class="bg-white rounded-2xl border border-coffee-100 shadow-sm p-6 sm:p-8">
        @include('dryers._form')

        <div class="flex flex-col-reverse sm:flex-row sm:items-center gap-3 pt-6 border-t border-coffee-100">
            <button type="submit" class="px-6 py-3 text-base font-bold text-white bg-coffee-700 hover:bg-coffee-800 rounded-lg transition shadow-sm w-full sm:w-auto">
                Salvar secador
            </button>
            <a href="{{ route('secadores.index') }}" class="text-center sm:text-left px-4 py-3 sm:py-0 text-sm font-semibold text-coffee-600 hover:text-coffee-900">Cancelar</a>
        </div>
    </form>
</div>
@endsection
