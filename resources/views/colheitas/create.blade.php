@extends('layouts.app')

@section('title', 'Registrar colheita')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-leaf-900">Registrar colheita</h1>
        <p class="text-sm text-leaf-500 mt-0.5">Lance a quantidade de café côco colhida numa área. O saldo da área fica disponível pra ser secado.</p>
    </div>

    <form method="POST" action="{{ route('colheitas.store') }}" class="bg-white rounded-2xl border border-leaf-100 shadow-sm p-6 space-y-5">
        @csrf

        <div>
            <label for="area_id" class="block text-sm font-bold text-leaf-900 mb-2">Área <span class="text-rose-500">*</span></label>
            <select id="area_id" name="area_id" required
                    class="w-full px-4 py-3 text-base rounded-lg border border-leaf-200 focus:border-leaf-500 focus:ring-4 focus:ring-leaf-500/15 outline-none transition bg-white">
                <option value="">Selecione</option>
                @foreach($areas as $a)
                    <option value="{{ $a->id }}">{{ $a->nome }} (saldo atual: {{ number_format($a->saldo_coco_kg, 2, ',', '.') }} kg côco)</option>
                @endforeach
            </select>
            @error('area_id')<p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="quantidade_kg" class="block text-sm font-bold text-leaf-900 mb-2">Quantidade colhida (kg) <span class="text-rose-500">*</span></label>
            <div class="relative">
                <input id="quantidade_kg" type="number" step="0.01" min="0.01" inputmode="decimal" name="quantidade_kg" required
                       placeholder="0,00"
                       class="w-full pl-4 pr-14 py-3 text-base rounded-lg border border-leaf-200 placeholder-leaf-300 focus:border-leaf-500 focus:ring-4 focus:ring-leaf-500/15 outline-none transition">
                <span class="absolute right-4 top-1/2 -translate-y-1/2 text-sm font-semibold text-leaf-500 pointer-events-none">kg</span>
            </div>
            @error('quantidade_kg')<p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="occurred_at" class="block text-sm font-bold text-leaf-900 mb-2">Data da colheita</label>
            <input id="occurred_at" type="datetime-local" name="occurred_at"
                   value="{{ now()->format('Y-m-d\TH:i') }}"
                   class="w-full px-4 py-3 text-base rounded-lg border border-leaf-200 focus:border-leaf-500 focus:ring-4 focus:ring-leaf-500/15 outline-none transition">
        </div>

        <div>
            <label for="observacao" class="block text-sm font-bold text-leaf-900 mb-2">Observação</label>
            <textarea id="observacao" name="observacao" rows="2" maxlength="500" placeholder="Opcional, ex: lote bem maduro, chuva durante a colheita…"
                      class="w-full px-4 py-3 text-base rounded-lg border border-leaf-200 placeholder-leaf-300 focus:border-leaf-500 focus:ring-4 focus:ring-leaf-500/15 outline-none transition"></textarea>
        </div>

        <button class="w-full sm:w-auto px-6 py-3 text-base font-bold text-white bg-leaf-700 hover:bg-leaf-800 rounded-lg transition shadow-sm">
            Registrar colheita
        </button>
    </form>
</div>
@endsection
