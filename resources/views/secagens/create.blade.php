@extends('layouts.app')

@section('title', 'Nova secagem')

@section('content')
<div class="max-w-xl">
    <h1 class="text-2xl font-bold text-coffee-900 mb-6">Nova secagem</h1>

    <form method="POST" action="{{ route('secagens.store') }}" class="bg-white rounded-xl border border-coffee-100 shadow-sm p-6 space-y-4">
        @csrf

        <div>
            <label class="block text-sm font-semibold text-coffee-800 mb-1.5">Data *</label>
            <input type="date" name="data" value="{{ old('data', now()->format('Y-m-d')) }}" required
                   class="w-full px-3.5 py-2.5 text-sm rounded-lg border border-coffee-200 focus:outline-none focus:ring-2 focus:ring-coffee-500">
            @error('data')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-semibold text-coffee-800 mb-1.5">Secador *</label>
            <input type="text" name="secador" value="{{ old('secador') }}" required maxlength="80" placeholder="Ex: Secador 1"
                   class="w-full px-3.5 py-2.5 text-sm rounded-lg border border-coffee-200 focus:outline-none focus:ring-2 focus:ring-coffee-500">
            @error('secador')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-semibold text-coffee-800 mb-1.5">Observações</label>
            <textarea name="observacoes" rows="3"
                      class="w-full px-3.5 py-2.5 text-sm rounded-lg border border-coffee-200 focus:outline-none focus:ring-2 focus:ring-coffee-500">{{ old('observacoes') }}</textarea>
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit" class="px-5 py-2.5 text-sm font-semibold text-white bg-coffee-700 hover:bg-coffee-800 rounded-lg transition shadow-sm">Criar rascunho</button>
            <a href="{{ route('secagens.index') }}" class="px-4 py-2.5 text-sm text-coffee-600 hover:text-coffee-900">Cancelar</a>
        </div>
    </form>
</div>
@endsection
