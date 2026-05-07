@extends('layouts.app')

@section('title', 'Editar cliente')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <p class="text-xs text-coffee-500 mb-1">
            <a href="{{ route('clientes.index') }}" class="hover:underline">Clientes</a> ·
            <a href="{{ route('clientes.show', $customer) }}" class="hover:underline">{{ $customer->nome }}</a> ·
            <span class="text-coffee-700">Editar</span>
        </p>
        <h1 class="text-2xl font-bold text-coffee-900">Editar cliente</h1>
    </div>

    <form method="POST" action="{{ route('clientes.update', $customer) }}" class="bg-white rounded-2xl border border-coffee-100 shadow-sm p-6 sm:p-8">
        @method('PUT')
        @include('customers._form')

        <div class="flex flex-col-reverse sm:flex-row sm:items-center gap-3 pt-6 border-t border-coffee-100">
            <button type="submit" class="px-6 py-3 text-base font-bold text-white bg-coffee-700 hover:bg-coffee-800 rounded-lg transition shadow-sm w-full sm:w-auto">
                Salvar alterações
            </button>
            <a href="{{ route('clientes.index') }}" class="text-center sm:text-left px-4 py-3 sm:py-0 text-sm font-semibold text-coffee-600 hover:text-coffee-900">Cancelar</a>
            @can('delete', $customer)
                <span class="hidden sm:flex flex-1"></span>
                <form method="POST" action="{{ route('clientes.destroy', $customer) }}"
                      data-confirm="Excluir este cliente?"
                      data-confirm-text="Esta ação não pode ser desfeita. As movimentações antigas continuam no histórico, mas o cliente sai das listas."
                      data-confirm-yes="Sim, excluir"
                      class="w-full sm:w-auto">
                    @csrf @method('DELETE')
                    <button class="w-full sm:w-auto px-4 py-3 text-sm font-semibold text-rose-600 hover:bg-rose-50 rounded-lg transition border border-transparent hover:border-rose-200">
                        Excluir cliente
                    </button>
                </form>
            @endcan
        </div>
    </form>
</div>
@endsection
