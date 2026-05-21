@extends('layouts.app')

@section('title', 'Nova área')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <p class="text-xs text-leaf-500 mb-1">
            <a href="{{ route('areas.index') }}" class="hover:underline">Áreas</a> · <span class="text-leaf-700">Nova</span>
        </p>
        <h1 class="text-2xl font-bold text-leaf-900">Nova área</h1>
        <p class="text-sm text-leaf-500 mt-1">Cadastre os talhões/lotes da <strong>sua roça</strong>. Ao secar o seu próprio café, você seleciona em qual área foi colhido, assim dá pra acompanhar quanto cada parte da roça produz.</p>
    </div>

    <form method="POST" action="{{ route('areas.store') }}" class="bg-white rounded-2xl border border-leaf-100 shadow-sm p-6 sm:p-8">
        @include('areas._form')

        <div class="flex flex-col-reverse sm:flex-row sm:items-center gap-3 pt-6 border-t border-leaf-100">
            <button type="submit" class="px-6 py-3 text-base font-bold text-white bg-leaf-700 hover:bg-leaf-800 rounded-lg transition shadow-sm w-full sm:w-auto">
                Salvar área
            </button>
            <a href="{{ route('areas.index') }}" class="text-center sm:text-left px-4 py-3 sm:py-0 text-sm font-semibold text-leaf-600 hover:text-leaf-900">Cancelar</a>
        </div>
    </form>
</div>
@endsection
