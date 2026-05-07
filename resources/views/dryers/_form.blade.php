@csrf

@php $D = $dryer ?? null; @endphp

<div class="border-b border-coffee-100 pb-5 mb-6">
    <h2 class="text-base font-bold text-coffee-900">Identificação do equipamento</h2>
    <p class="text-sm text-coffee-500 mt-0.5">Como este secador será exibido nas listas e nos relatórios.</p>
</div>

<div class="space-y-5 mb-6">
    <div>
        <label for="nome" class="block text-sm font-bold text-coffee-900 mb-2">
            Nome <span class="text-rose-500">*</span>
        </label>
        <input id="nome" type="text" name="nome" required maxlength="80" autofocus
               value="{{ old('nome', $D?->nome) }}"
               placeholder="ex: Secador 1, Secador grande, Pinhalense"
               class="w-full px-4 py-3 text-base rounded-lg border border-coffee-200 placeholder-coffee-300 focus:border-coffee-500 focus:ring-4 focus:ring-coffee-500/15 outline-none transition">
        <p class="mt-1.5 text-sm text-coffee-500">Apelido único — escolha algo que toda a equipe reconheça.</p>
        @error('nome')<p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div class="grid sm:grid-cols-2 gap-5">
        <div>
            <label for="modelo" class="block text-sm font-bold text-coffee-900 mb-2">Modelo</label>
            <input id="modelo" type="text" name="modelo" maxlength="80"
                   value="{{ old('modelo', $D?->modelo) }}"
                   placeholder="ex: Pinhalense SRE 5, Palini PR4"
                   class="w-full px-4 py-3 text-base rounded-lg border border-coffee-200 placeholder-coffee-300 focus:border-coffee-500 focus:ring-4 focus:ring-coffee-500/15 outline-none transition">
            <p class="mt-1.5 text-sm text-coffee-500">Marca e modelo, se quiser registrar.</p>
        </div>

        <div>
            <label for="capacidade_kg" class="block text-sm font-bold text-coffee-900 mb-2">Capacidade</label>
            <div class="relative">
                <input id="capacidade_kg" type="number" step="0.001" min="0" inputmode="decimal" name="capacidade_kg"
                       value="{{ old('capacidade_kg', $D?->capacidade_kg) }}"
                       placeholder="ex: 2500"
                       class="w-full pl-4 pr-14 py-3 text-base rounded-lg border border-coffee-200 placeholder-coffee-300 focus:border-coffee-500 focus:ring-4 focus:ring-coffee-500/15 outline-none transition">
                <span class="absolute right-4 top-1/2 -translate-y-1/2 text-sm font-semibold text-coffee-500 pointer-events-none">kg</span>
            </div>
            <p class="mt-1.5 text-sm text-coffee-500">Capacidade máxima por carga, em quilos. Opcional.</p>
            @error('capacidade_kg')<p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>@enderror
        </div>
    </div>

    <div>
        <label for="observacoes" class="block text-sm font-bold text-coffee-900 mb-2">Observações</label>
        <textarea id="observacoes" name="observacoes" rows="3"
                  placeholder="Ex: localização, instruções de operação, manutenções recentes…"
                  class="w-full px-4 py-3 text-base rounded-lg border border-coffee-200 placeholder-coffee-300 focus:border-coffee-500 focus:ring-4 focus:ring-coffee-500/15 outline-none transition">{{ old('observacoes', $D?->observacoes) }}</textarea>
    </div>
</div>

<div class="border-b border-coffee-100 pb-5 mb-6">
    <h2 class="text-base font-bold text-coffee-900">Disponibilidade</h2>
    <p class="text-sm text-coffee-500 mt-0.5">Controle se este secador pode ser usado em novas secagens.</p>
</div>

<div class="mb-6">
    <label class="flex items-start gap-3 p-4 rounded-lg border-2 border-coffee-200 cursor-pointer hover:bg-coffee-50/50 transition has-[:checked]:border-coffee-500 has-[:checked]:bg-coffee-50">
        <input type="hidden" name="ativo" value="0">
        <input type="checkbox" name="ativo" value="1" {{ old('ativo', $D?->ativo ?? true) ? 'checked' : '' }}
               class="mt-0.5 w-5 h-5 rounded border-coffee-300 text-coffee-700 focus:ring-coffee-500">
        <div>
            <span class="block text-sm font-bold text-coffee-900">Secador ativo</span>
            <span class="block text-sm text-coffee-500 mt-0.5">
                Marque para que este secador apareça no menu ao criar uma nova secagem. Desmarque se quebrou ou está fora de uso.
            </span>
        </div>
    </label>
</div>
