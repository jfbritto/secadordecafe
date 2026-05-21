@extends('layouts.app')

@section('title', 'Nova área')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <p class="text-xs text-coffee-500 mb-1">
            <a href="{{ route('areas.index') }}" class="hover:underline">Áreas</a> · <span class="text-coffee-700">Nova</span>
        </p>
        <h1 class="text-2xl font-bold text-coffee-900">Nova área</h1>
        <p class="text-sm text-coffee-500 mt-1">Cada talhão ou lote da roça. Ao criar uma secagem, você poderá vinculá-la a uma área e ter o controle de produção por região.</p>
    </div>

    <form method="POST" action="{{ route('areas.store') }}" class="bg-white rounded-2xl border border-coffee-100 shadow-sm p-6 sm:p-8">
        @include('areas._form')

        <div class="flex flex-col-reverse sm:flex-row sm:items-center gap-3 pt-6 border-t border-coffee-100">
            <button type="submit" class="px-6 py-3 text-base font-bold text-white bg-coffee-700 hover:bg-coffee-800 rounded-lg transition shadow-sm w-full sm:w-auto">
                Salvar área
            </button>
            <a href="{{ route('areas.index') }}" class="text-center sm:text-left px-4 py-3 sm:py-0 text-sm font-semibold text-coffee-600 hover:text-coffee-900">Cancelar</a>
        </div>
    </form>
</div>
@endsection
