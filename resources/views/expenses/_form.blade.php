@csrf

@php $E = $expense ?? null; @endphp

<div class="grid sm:grid-cols-2 gap-4 mb-4">
    <div>
        <label class="block text-sm font-semibold text-coffee-800 mb-1.5">Data *</label>
        <input type="date" name="data" required value="{{ old('data', $E ? $E->data->format('Y-m-d') : now()->format('Y-m-d')) }}"
               class="w-full px-3.5 py-2.5 text-sm rounded-lg border border-coffee-200 focus:outline-none focus:ring-2 focus:ring-coffee-500">
        @error('data')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-semibold text-coffee-800 mb-1.5">Categoria *</label>
        <select name="expense_category_id" required class="w-full px-3.5 py-2.5 text-sm rounded-lg border border-coffee-200 focus:outline-none focus:ring-2 focus:ring-coffee-500">
            <option value="">— selecione —</option>
            @foreach($categories as $c)
                <option value="{{ $c->id }}" @selected(old('expense_category_id', $E?->expense_category_id) == $c->id)>{{ $c->nome }}</option>
            @endforeach
        </select>
        @error('expense_category_id')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
        <p class="mt-1 text-xs text-coffee-500">
            Não viu a categoria? <a href="{{ route('despesas.categorias.create') }}" class="text-coffee-700 font-semibold hover:underline">Cadastrar nova</a>.
        </p>
    </div>
</div>

<div class="mb-4">
    <label class="block text-sm font-semibold text-coffee-800 mb-1.5">Descrição *</label>
    <input type="text" name="descricao" maxlength="200" required value="{{ old('descricao', $E?->descricao) }}"
           class="w-full px-3.5 py-2.5 text-sm rounded-lg border border-coffee-200 focus:outline-none focus:ring-2 focus:ring-coffee-500">
    @error('descricao')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
</div>

<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-4">
    <div>
        <label class="block text-xs font-semibold text-coffee-700 mb-1.5">Unidade</label>
        <input type="text" name="unidade" maxlength="20" value="{{ old('unidade', $E?->unidade) }}"
               class="w-full px-3 py-2 text-sm rounded-lg border border-coffee-200 focus:outline-none focus:ring-2 focus:ring-coffee-500">
    </div>
    <div>
        <label class="block text-xs font-semibold text-coffee-700 mb-1.5">Quantidade</label>
        <input type="number" step="0.001" min="0.001" name="quantidade" value="{{ old('quantidade', $E?->quantidade ?? 1) }}"
               class="w-full px-3 py-2 text-sm rounded-lg border border-coffee-200 focus:outline-none focus:ring-2 focus:ring-coffee-500">
    </div>
    <div>
        <label class="block text-xs font-semibold text-coffee-700 mb-1.5">Vlr. unitário</label>
        <input type="number" step="0.01" min="0" name="valor_unitario" value="{{ old('valor_unitario', $E?->valor_unitario ?? 0) }}"
               class="w-full px-3 py-2 text-sm rounded-lg border border-coffee-200 focus:outline-none focus:ring-2 focus:ring-coffee-500">
    </div>
    <div>
        <label class="block text-xs font-semibold text-coffee-700 mb-1.5">Vlr. total *</label>
        <input type="number" step="0.01" min="0.01" name="valor_total" required value="{{ old('valor_total', $E?->valor_total) }}"
               class="w-full px-3 py-2 text-sm rounded-lg border border-coffee-200 focus:outline-none focus:ring-2 focus:ring-coffee-500">
        @error('valor_total')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>
</div>

<div class="mb-4">
    <label class="block text-sm font-semibold text-coffee-800 mb-1.5">Observações</label>
    <textarea name="observacoes" rows="2"
              class="w-full px-3.5 py-2.5 text-sm rounded-lg border border-coffee-200 focus:outline-none focus:ring-2 focus:ring-coffee-500">{{ old('observacoes', $E?->observacoes) }}</textarea>
</div>
