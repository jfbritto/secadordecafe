@csrf

@php $C = $category ?? null; @endphp

<div class="border-b border-leaf-100 pb-5 mb-6">
    <h2 class="text-base font-bold text-leaf-900">Categoria de despesa</h2>
    <p class="text-sm text-leaf-500 mt-0.5">Use categorias para agrupar despesas e ver totais nos relatórios.</p>
</div>

<div class="space-y-5 mb-6">
    <div>
        <label for="nome" class="block text-sm font-bold text-leaf-900 mb-2">
            Nome <span class="text-rose-500">*</span>
        </label>
        <input id="nome" type="text" name="nome" required maxlength="80" autofocus
               value="{{ old('nome', $C?->nome) }}"
               placeholder="ex: Combustível, Adubo, Pró-labore"
               class="w-full px-4 py-3 text-base rounded-lg border border-leaf-200 placeholder-leaf-300 focus:border-leaf-500 focus:ring-4 focus:ring-leaf-500/15 outline-none transition">
        <p class="mt-1.5 text-sm text-leaf-500">Aparece no menu ao cadastrar uma despesa e nos totais por categoria.</p>
        @error('nome')<p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="observacoes" class="block text-sm font-bold text-leaf-900 mb-2">Observações</label>
        <textarea id="observacoes" name="observacoes" rows="3"
                  placeholder="O que entra nesta categoria, regras internas, etc."
                  class="w-full px-4 py-3 text-base rounded-lg border border-leaf-200 placeholder-leaf-300 focus:border-leaf-500 focus:ring-4 focus:ring-leaf-500/15 outline-none transition">{{ old('observacoes', $C?->observacoes) }}</textarea>
        <p class="mt-1.5 text-sm text-leaf-500">Opcional. Útil pra documentar o que considerar nesta categoria.</p>
    </div>

    <label class="flex items-start gap-3 p-4 rounded-lg border-2 border-leaf-200 cursor-pointer hover:bg-leaf-50/50 transition has-[:checked]:border-leaf-500 has-[:checked]:bg-leaf-50">
        <input type="hidden" name="ativo" value="0">
        <input type="checkbox" name="ativo" value="1" {{ old('ativo', $C?->ativo ?? true) ? 'checked' : '' }}
               class="mt-0.5 w-5 h-5 rounded border-leaf-300 text-leaf-700 focus:ring-leaf-500">
        <div>
            <span class="block text-sm font-bold text-leaf-900">Categoria ativa</span>
            <span class="block text-sm text-leaf-500 mt-0.5">
                Marque para que esta categoria apareça no menu ao cadastrar uma nova despesa. Categorias inativas continuam aparecendo nos relatórios antigos.
            </span>
        </div>
    </label>
</div>
