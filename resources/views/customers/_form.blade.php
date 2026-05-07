@csrf

@php $C = $customer ?? null; @endphp

<div class="grid sm:grid-cols-2 gap-4 mb-4">
    <div class="sm:col-span-2">
        <label class="block text-sm font-semibold text-coffee-800 mb-1.5">Nome *</label>
        <input type="text" name="nome" value="{{ old('nome', $C?->nome) }}" required maxlength="150"
               class="w-full px-3.5 py-2.5 text-sm rounded-lg border border-coffee-200 focus:outline-none focus:ring-2 focus:ring-coffee-500 focus:border-coffee-500">
        @error('nome')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-semibold text-coffee-800 mb-1.5">Telefone</label>
        <input type="text" name="telefone" value="{{ old('telefone', $C?->telefone) }}" maxlength="30"
               class="w-full px-3.5 py-2.5 text-sm rounded-lg border border-coffee-200 focus:outline-none focus:ring-2 focus:ring-coffee-500 focus:border-coffee-500">
    </div>

    <div>
        <label class="block text-sm font-semibold text-coffee-800 mb-1.5">CPF / CNPJ</label>
        <input type="text" name="cpf_cnpj" value="{{ old('cpf_cnpj', $C?->cpf_cnpj) }}" maxlength="20"
               class="w-full px-3.5 py-2.5 text-sm rounded-lg border border-coffee-200 focus:outline-none focus:ring-2 focus:ring-coffee-500 focus:border-coffee-500">
        @error('cpf_cnpj')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div class="sm:col-span-2">
        <label class="block text-sm font-semibold text-coffee-800 mb-1.5">Saldo inicial de café (kg)</label>
        <input type="number" step="0.001" min="0" name="saldo_cafe_kg" value="{{ old('saldo_cafe_kg', $C?->saldo_cafe_kg ?? 0) }}"
               class="w-full px-3.5 py-2.5 text-sm rounded-lg border border-coffee-200 focus:outline-none focus:ring-2 focus:ring-coffee-500 focus:border-coffee-500">
        <p class="mt-1 text-xs text-coffee-500">A partir do uso, este valor é controlado por movimentações automáticas.</p>
    </div>

    <div class="sm:col-span-2">
        <label class="block text-sm font-semibold text-coffee-800 mb-1.5">Observações</label>
        <textarea name="observacoes" rows="3"
                  class="w-full px-3.5 py-2.5 text-sm rounded-lg border border-coffee-200 focus:outline-none focus:ring-2 focus:ring-coffee-500 focus:border-coffee-500">{{ old('observacoes', $C?->observacoes) }}</textarea>
    </div>
</div>
