@extends('layouts.app')

@section('title', 'Editar secador')

@section('content')
<div class="max-w-2xl">
    <h1 class="text-2xl font-bold text-coffee-900 mb-6">Editar secador</h1>
    <form method="POST" action="{{ route('secadores.update', $dryer) }}" class="bg-white rounded-xl border border-coffee-100 shadow-sm p-6">
        @method('PUT')
        @include('dryers._form')
        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="px-5 py-2.5 text-sm font-semibold text-white bg-coffee-700 hover:bg-coffee-800 rounded-lg transition shadow-sm">Salvar</button>
            <a href="{{ route('secadores.index') }}" class="px-4 py-2.5 text-sm text-coffee-600 hover:text-coffee-900">Cancelar</a>
            @can('delete', $dryer)
                <span class="flex-1"></span>
                <form method="POST" action="{{ route('secadores.destroy', $dryer) }}" onsubmit="return confirm('Excluir este secador? (Só é possível se não houver secagens vinculadas.)');" class="inline">
                    @csrf @method('DELETE')
                    <button class="px-3 py-2 text-sm font-semibold text-rose-600 hover:bg-rose-50 rounded-lg transition">Excluir</button>
                </form>
            @endcan
        </div>
    </form>

    @if($dryer->secagens()->exists())
        <p class="mt-4 text-xs text-coffee-500">
            Este secador possui {{ $dryer->secagens()->count() }} secagem(ns) registrada(s).
            Para impedir uso em novas secagens, desmarque <strong>"Secador ativo"</strong> em vez de excluir.
        </p>
    @endif
</div>
@endsection
