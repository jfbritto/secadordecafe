@extends('layouts.app')

@section('title', 'Configurações da fazenda')

@section('content')
<div class="max-w-2xl">
    <h1 class="text-2xl font-bold text-coffee-900 mb-6">Configurações da fazenda</h1>

    <form method="POST" action="{{ route('fazenda.update') }}" class="bg-white rounded-xl border border-coffee-100 shadow-sm p-6 space-y-4">
        @csrf @method('PUT')

        <div>
            <label class="block text-sm font-semibold text-coffee-800 mb-1.5">Nome *</label>
            <input type="text" name="nome" value="{{ old('nome', $farm->nome) }}" required maxlength="150"
                   class="w-full px-3.5 py-2.5 text-sm rounded-lg border border-coffee-200 focus:outline-none focus:ring-2 focus:ring-coffee-500">
            @error('nome')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-semibold text-coffee-800 mb-1.5">Telefone</label>
            <input type="text" name="telefone" value="{{ old('telefone', $farm->telefone) }}" maxlength="30"
                   class="w-full px-3.5 py-2.5 text-sm rounded-lg border border-coffee-200 focus:outline-none focus:ring-2 focus:ring-coffee-500">
        </div>

        <div class="grid grid-cols-3 gap-3">
            <div class="col-span-2">
                <label class="block text-sm font-semibold text-coffee-800 mb-1.5">Cidade</label>
                <input type="text" name="cidade" value="{{ old('cidade', $farm->cidade) }}" maxlength="120"
                       class="w-full px-3.5 py-2.5 text-sm rounded-lg border border-coffee-200 focus:outline-none focus:ring-2 focus:ring-coffee-500">
            </div>
            <div>
                <label class="block text-sm font-semibold text-coffee-800 mb-1.5">UF</label>
                <input type="text" name="estado" value="{{ old('estado', $farm->estado) }}" maxlength="2"
                       class="w-full px-3.5 py-2.5 text-sm rounded-lg border border-coffee-200 focus:outline-none focus:ring-2 focus:ring-coffee-500 uppercase">
            </div>
        </div>

        <button type="submit" class="px-5 py-2.5 text-sm font-semibold text-white bg-coffee-700 hover:bg-coffee-800 rounded-lg transition shadow-sm">Salvar</button>
    </form>

    <div class="mt-4 bg-white rounded-xl border border-coffee-100 p-5 text-sm text-coffee-600">
        <strong class="text-coffee-900">Status atual:</strong>
        @php $cls = match($farm->status){'active'=>'bg-emerald-100 text-emerald-700','blocked'=>'bg-rose-100 text-rose-700','past_due'=>'bg-amber-100 text-amber-700', default=>'bg-amber-100 text-amber-700'}; @endphp
        <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $cls }} ml-1">{{ strtoupper($farm->status) }}</span>
        <span class="block text-xs text-coffee-500 mt-1">Slug: {{ $farm->slug }} · Criada em {{ $farm->created_at->format('d/m/Y') }}</span>
    </div>
</div>
@endsection
