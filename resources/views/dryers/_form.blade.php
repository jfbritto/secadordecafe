@csrf

@php $D = $dryer ?? null; @endphp

<div class="space-y-4 mb-4">
    <div>
        <label class="block text-sm font-semibold text-coffee-800 mb-1.5">Nome *</label>
        <input type="text" name="nome" value="{{ old('nome', $D?->nome) }}" required maxlength="80" placeholder="Ex: Secador 1"
               class="w-full px-3.5 py-2.5 text-sm rounded-lg border border-coffee-200 focus:outline-none focus:ring-2 focus:ring-coffee-500 focus:border-coffee-500">
        @error('nome')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div class="grid sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-semibold text-coffee-800 mb-1.5">Modelo</label>
            <input type="text" name="modelo" value="{{ old('modelo', $D?->modelo) }}" maxlength="80" placeholder="Ex: Pinhalense"
                   class="w-full px-3.5 py-2.5 text-sm rounded-lg border border-coffee-200 focus:outline-none focus:ring-2 focus:ring-coffee-500 focus:border-coffee-500">
        </div>
        <div>
            <label class="block text-sm font-semibold text-coffee-800 mb-1.5">Capacidade (kg)</label>
            <input type="number" step="0.001" min="0" name="capacidade_kg" value="{{ old('capacidade_kg', $D?->capacidade_kg) }}"
                   class="w-full px-3.5 py-2.5 text-sm rounded-lg border border-coffee-200 focus:outline-none focus:ring-2 focus:ring-coffee-500 focus:border-coffee-500">
            @error('capacidade_kg')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
        </div>
    </div>

    <div>
        <label class="block text-sm font-semibold text-coffee-800 mb-1.5">Observações</label>
        <textarea name="observacoes" rows="3"
                  class="w-full px-3.5 py-2.5 text-sm rounded-lg border border-coffee-200 focus:outline-none focus:ring-2 focus:ring-coffee-500 focus:border-coffee-500">{{ old('observacoes', $D?->observacoes) }}</textarea>
    </div>

    <label class="flex items-center gap-2 text-sm text-coffee-700">
        <input type="hidden" name="ativo" value="0">
        <input type="checkbox" name="ativo" value="1" {{ old('ativo', $D?->ativo ?? true) ? 'checked' : '' }}
               class="rounded border-coffee-300 text-coffee-700 focus:ring-coffee-500">
        Secador ativo (disponível para novas secagens)
    </label>
</div>
