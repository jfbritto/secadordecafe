@extends('layouts.app')

@section('title', 'Novo cliente')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <p class="text-xs text-leaf-500 mb-1">
            <a href="{{ route('clientes.index') }}" class="hover:underline">Clientes</a> · <span class="text-leaf-700">Novo</span>
        </p>
        <h1 class="text-2xl font-bold text-leaf-900">Novo cliente</h1>
        <p class="text-sm text-leaf-500 mt-1">Preencha os dados do produtor. Só o nome é obrigatório.</p>
    </div>

    <form method="POST" action="{{ route('clientes.store') }}" class="bg-white rounded-2xl border border-leaf-100 shadow-sm p-6 sm:p-8">
        @include('customers._form')

        <div class="flex flex-col-reverse sm:flex-row sm:items-center gap-3 pt-6 border-t border-leaf-100">
            <button type="submit" class="px-6 py-3 text-base font-bold text-white bg-leaf-700 hover:bg-leaf-800 rounded-lg transition shadow-sm w-full sm:w-auto">
                Salvar cliente
            </button>
            <a href="{{ route('clientes.index') }}" class="text-center sm:text-left px-4 py-3 sm:py-0 text-sm font-semibold text-leaf-600 hover:text-leaf-900">Cancelar</a>
        </div>
    </form>
</div>
@endsection
