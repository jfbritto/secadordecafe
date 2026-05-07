@csrf

@php $C = $customer ?? null; @endphp

{{-- Identificação --}}
<div class="border-b border-coffee-100 pb-5 mb-6">
    <h2 class="text-base font-bold text-coffee-900">Dados do produtor</h2>
    <p class="text-sm text-coffee-500 mt-0.5">Informações que identificam o cliente nas listas e relatórios.</p>
</div>

<div class="space-y-5 mb-6">
    <div>
        <label for="nome" class="block text-sm font-bold text-coffee-900 mb-2">
            Nome <span class="text-rose-500">*</span>
        </label>
        <input id="nome" type="text" name="nome" required maxlength="150" autofocus
               value="{{ old('nome', $C?->nome) }}"
               placeholder="ex: João da Silva ou Sítio das Palmeiras"
               class="w-full px-4 py-3 text-base rounded-lg border border-coffee-200 placeholder-coffee-300 focus:border-coffee-500 focus:ring-4 focus:ring-coffee-500/15 outline-none transition">
        <p class="mt-1.5 text-sm text-coffee-500">Nome completo da pessoa ou nome da propriedade.</p>
        @error('nome')<p class="mt-2 text-sm font-medium text-rose-600 flex items-start gap-1.5">
            <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            {{ $message }}</p>@enderror
    </div>

    <div class="grid sm:grid-cols-2 gap-5">
        <div>
            <label for="telefone" class="block text-sm font-bold text-coffee-900 mb-2">Telefone</label>
            <input id="telefone" type="tel" name="telefone" maxlength="20" inputmode="numeric"
                   data-mask="phone"
                   value="{{ old('telefone', $C?->telefone) }}"
                   placeholder="(00) 00000-0000"
                   class="w-full px-4 py-3 text-base rounded-lg border border-coffee-200 placeholder-coffee-300 focus:border-coffee-500 focus:ring-4 focus:ring-coffee-500/15 outline-none transition">
            <p class="mt-1.5 text-sm text-coffee-500">DDD + número. WhatsApp se houver.</p>
        </div>

        <div>
            <label for="cpf_cnpj" class="block text-sm font-bold text-coffee-900 mb-2">CPF ou CNPJ</label>
            <input id="cpf_cnpj" type="text" name="cpf_cnpj" maxlength="20" inputmode="numeric"
                   data-mask="cpfcnpj"
                   value="{{ old('cpf_cnpj', $C?->cpf_cnpj) }}"
                   placeholder="000.000.000-00"
                   class="w-full px-4 py-3 text-base rounded-lg border border-coffee-200 placeholder-coffee-300 focus:border-coffee-500 focus:ring-4 focus:ring-coffee-500/15 outline-none transition">
            <p class="mt-1.5 text-sm text-coffee-500">Vai formatar sozinho. Pessoa física ou jurídica.</p>
            @error('cpf_cnpj')<p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>@enderror
        </div>
    </div>
</div>

{{-- Saldo inicial --}}
<div class="border-b border-coffee-100 pb-5 mb-6">
    <h2 class="text-base font-bold text-coffee-900">Saldo de café</h2>
    <p class="text-sm text-coffee-500 mt-0.5">Quanto café este cliente já tem em estoque na fazenda agora.</p>
</div>

<div class="mb-6">
    <label for="saldo_cafe_kg" class="block text-sm font-bold text-coffee-900 mb-2">Saldo inicial (kg)</label>
    <div class="relative">
        <input id="saldo_cafe_kg" type="number" step="0.001" min="0" inputmode="decimal" name="saldo_cafe_kg"
               value="{{ old('saldo_cafe_kg', $C?->saldo_cafe_kg ?? 0) }}"
               placeholder="0,000"
               class="w-full pl-4 pr-14 py-3 text-base rounded-lg border border-coffee-200 placeholder-coffee-300 focus:border-coffee-500 focus:ring-4 focus:ring-coffee-500/15 outline-none transition">
        <span class="absolute right-4 top-1/2 -translate-y-1/2 text-sm font-semibold text-coffee-500 pointer-events-none">kg</span>
    </div>
    <p class="mt-1.5 text-sm text-coffee-500">
        Use <strong>0</strong> se ainda não há café em estoque.
        Depois, este saldo passa a ser controlado automaticamente pelas movimentações (entradas, secagens, saídas).
    </p>
</div>

{{-- Observações --}}
<div class="mb-6">
    <label for="observacoes" class="block text-sm font-bold text-coffee-900 mb-2">Observações</label>
    <textarea id="observacoes" name="observacoes" rows="3" maxlength="2000"
              placeholder="Anotações livres: localização, contato secundário, particularidades…"
              class="w-full px-4 py-3 text-base rounded-lg border border-coffee-200 placeholder-coffee-300 focus:border-coffee-500 focus:ring-4 focus:ring-coffee-500/15 outline-none transition">{{ old('observacoes', $C?->observacoes) }}</textarea>
    <p class="mt-1.5 text-sm text-coffee-500">Opcional. Aparece na ficha do cliente.</p>
</div>
