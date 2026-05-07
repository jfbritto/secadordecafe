@extends('layouts.app')

@section('title', 'Nova despesa')

@section('content')
<div class="max-w-2xl bg-white rounded-2xl border border-coffee-100 shadow-sm p-10 text-center">
    <div class="w-12 h-12 rounded-full bg-amber-100 mx-auto mb-4 flex items-center justify-center">
        <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
    </div>
    <h1 class="text-xl font-bold text-coffee-900 mb-2">Nenhuma categoria ativa</h1>
    <p class="text-sm text-coffee-500 mb-6">Cadastre pelo menos uma categoria de despesa antes de registrar lançamentos.</p>
    @can('create', App\Models\ExpenseCategory::class)
        <a href="{{ route('despesas.categorias.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-coffee-700 hover:bg-coffee-800 rounded-lg transition shadow-sm">
            Cadastrar categoria
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
        </a>
    @endcan
</div>
@endsection
