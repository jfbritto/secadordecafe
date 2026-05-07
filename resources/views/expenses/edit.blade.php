@extends('layouts.app')

@section('title', 'Editar despesa')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <p class="text-xs text-coffee-500 mb-1">
            <a href="{{ route('despesas.index') }}" class="hover:underline">Despesas</a> · <span class="text-coffee-700">Editar</span>
        </p>
        <h1 class="text-2xl font-bold text-coffee-900">Editar despesa</h1>
    </div>

    <form method="POST" action="{{ route('despesas.update', $expense) }}" class="bg-white rounded-2xl border border-coffee-100 shadow-sm p-6 sm:p-8">
        @method('PUT')
        @include('expenses._form')

        <div class="flex flex-col-reverse sm:flex-row sm:items-center gap-3 pt-6 border-t border-coffee-100">
            <button type="submit" class="px-6 py-3 text-base font-bold text-white bg-coffee-700 hover:bg-coffee-800 rounded-lg transition shadow-sm w-full sm:w-auto">
                Salvar alterações
            </button>
            <a href="{{ route('despesas.index') }}" class="text-center sm:text-left px-4 py-3 sm:py-0 text-sm font-semibold text-coffee-600 hover:text-coffee-900">Cancelar</a>
            @can('delete', $expense)
                <span class="hidden sm:flex flex-1"></span>
                <form method="POST" action="{{ route('despesas.destroy', $expense) }}"
                      data-confirm="Excluir esta despesa?"
                      data-confirm-text="Esta ação não pode ser desfeita."
                      data-confirm-yes="Sim, excluir"
                      class="w-full sm:w-auto">
                    @csrf @method('DELETE')
                    <button class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-3 text-sm font-semibold text-rose-600 hover:bg-rose-50 rounded-lg transition border border-transparent hover:border-rose-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        Excluir despesa
                    </button>
                </form>
            @endcan
        </div>
    </form>
</div>
@endsection
