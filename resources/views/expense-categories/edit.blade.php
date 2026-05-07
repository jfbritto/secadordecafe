@extends('layouts.app')

@section('title', 'Editar categoria')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <p class="text-xs text-coffee-500 mb-1">
            <a href="{{ route('despesas.index') }}" class="hover:underline">Despesas</a> ·
            <a href="{{ route('despesas.categorias.index') }}" class="hover:underline">Categorias</a> ·
            <span class="text-coffee-700">{{ $category->nome }}</span>
        </p>
        <h1 class="text-2xl font-bold text-coffee-900">Editar categoria</h1>
    </div>

    <form method="POST" action="{{ route('despesas.categorias.update', $category) }}" class="bg-white rounded-2xl border border-coffee-100 shadow-sm p-6 sm:p-8">
        @method('PUT')
        @include('expense-categories._form')

        <div class="flex flex-col-reverse sm:flex-row sm:items-center gap-3 pt-6 border-t border-coffee-100">
            <button type="submit" class="px-6 py-3 text-base font-bold text-white bg-coffee-700 hover:bg-coffee-800 rounded-lg transition shadow-sm w-full sm:w-auto">
                Salvar alterações
            </button>
            <a href="{{ route('despesas.categorias.index') }}" class="text-center sm:text-left px-4 py-3 sm:py-0 text-sm font-semibold text-coffee-600 hover:text-coffee-900">Cancelar</a>
        </div>
    </form>

    @if($category->expenses()->exists())
        <div class="mt-4 px-4 py-3 rounded-lg bg-amber-50 border border-amber-200 text-sm text-amber-900">
            Esta categoria possui <strong>{{ $category->expenses()->count() }}</strong> despesa(s) registrada(s).
            Para tirá-la de circulação sem perder o histórico, desmarque <strong>"Categoria ativa"</strong> em vez de excluir.
        </div>
    @endif

    @can('delete', $category)
        <div class="mt-6 bg-white rounded-2xl border border-rose-200 shadow-sm p-6 sm:p-8">
            <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                <div class="flex-1">
                    <h2 class="text-base font-bold text-rose-900">Zona de exclusão</h2>
                    <p class="text-sm text-rose-600 mt-0.5">Remover a categoria permanentemente. Só funciona se não houver despesas vinculadas.</p>
                </div>
                <form method="POST" action="{{ route('despesas.categorias.destroy', $category) }}"
                      data-confirm="Excluir esta categoria?"
                      data-confirm-text="Só é possível se não houver despesas vinculadas. Para tirar de circulação sem perder o histórico, desmarque 'Categoria ativa' em vez de excluir."
                      data-confirm-yes="Sim, excluir"
                      class="w-full sm:w-auto">
                    @csrf @method('DELETE')
                    <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-3 text-sm font-bold text-white bg-rose-600 hover:bg-rose-700 rounded-lg transition shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        Excluir categoria
                    </button>
                </form>
            </div>
        </div>
    @endcan
</div>
@endsection
