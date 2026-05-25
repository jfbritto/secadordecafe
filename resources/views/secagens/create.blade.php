@extends('layouts.app')

@section('title', 'Nova secagem')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <p class="text-xs text-leaf-500 mb-1">
            <a href="{{ route('secagens.index') }}" class="hover:underline">Secagens</a> · <span class="text-leaf-700">Nova</span>
        </p>
        <h1 class="text-2xl font-bold text-leaf-900">Nova secagem</h1>
        <p class="text-sm text-leaf-500 mt-1">Crie um rascunho. Depois você adiciona os clientes participantes e conclui pra debitar os saldos.</p>
    </div>

    <form method="POST" action="{{ route('secagens.store') }}" class="bg-white rounded-2xl border border-leaf-100 shadow-sm p-6 sm:p-8">
        @csrf

        <div class="border-b border-leaf-100 pb-5 mb-6">
            <h2 class="text-base font-bold text-leaf-900">Dados gerais</h2>
            <p class="text-sm text-leaf-500 mt-0.5">Quando e onde a secagem foi realizada.</p>
        </div>

        <div class="grid sm:grid-cols-2 gap-5 mb-6">
            <div>
                <label for="data" class="block text-sm font-bold text-leaf-900 mb-2">
                    Data <span class="text-rose-500">*</span>
                </label>
                <input id="data" type="date" name="data" required value="{{ old('data', now()->format('Y-m-d')) }}"
                       class="w-full px-4 py-3 text-base rounded-lg border border-leaf-200 focus:border-leaf-500 focus:ring-4 focus:ring-leaf-500/15 outline-none transition">
                <p class="mt-1.5 text-sm text-leaf-500">Dia em que a secagem aconteceu.</p>
                @error('data')<p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="dryer_id" class="block text-sm font-bold text-leaf-900 mb-2">
                    Secador <span class="text-rose-500">*</span>
                </label>
                <select id="dryer_id" name="dryer_id" required
                        class="w-full px-4 py-3 text-base rounded-lg border border-leaf-200 focus:border-leaf-500 focus:ring-4 focus:ring-leaf-500/15 outline-none transition bg-white">
                    <option value="">Selecione</option>
                    @foreach($dryers as $d)
                        <option value="{{ $d->id }}" @selected(old('dryer_id') == $d->id)>{{ $d->nome }}</option>
                    @endforeach
                </select>
                <p class="mt-1.5 text-sm text-leaf-500">
                    Equipamento usado. Não viu? <a href="{{ route('secadores.create') }}" class="text-leaf-700 font-semibold hover:underline">Cadastrar novo</a>.
                </p>
                @error('dryer_id')<p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="bg-leaf-50/50 border border-leaf-100 rounded-lg p-4 mb-6 text-sm text-leaf-700">
            <strong>Misturando café?</strong> Depois de criar o rascunho, você adiciona cada lote escolhendo a origem:
            🧑 <strong>cliente externo</strong> ou 🌱 <strong>área da sua roça</strong>. Pode misturar os dois no mesmo ciclo do secador.
        </div>

        <div class="mb-6">
            <label for="observacoes" class="block text-sm font-bold text-leaf-900 mb-2">Observações</label>
            <textarea id="observacoes" name="observacoes" rows="3"
                      placeholder="Ex: tempo de secagem, temperatura, condições do café…"
                      class="w-full px-4 py-3 text-base rounded-lg border border-leaf-200 placeholder-leaf-300 focus:border-leaf-500 focus:ring-4 focus:ring-leaf-500/15 outline-none transition">{{ old('observacoes') }}</textarea>
            <p class="mt-1.5 text-sm text-leaf-500">Opcional. Útil pra registrar particularidades da secagem.</p>
        </div>

        <div class="flex flex-col-reverse sm:flex-row sm:items-center gap-3 pt-6 border-t border-leaf-100">
            <button type="submit" class="px-6 py-3 text-base font-bold text-white bg-leaf-700 hover:bg-leaf-800 rounded-lg transition shadow-sm w-full sm:w-auto">
                Criar rascunho
            </button>
            <a href="{{ route('secagens.index') }}" class="text-center sm:text-left px-4 py-3 sm:py-0 text-sm font-semibold text-leaf-600 hover:text-leaf-900">Cancelar</a>
        </div>
    </form>
</div>
@endsection
