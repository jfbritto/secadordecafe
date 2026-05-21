@csrf

@php
    $E = $expense ?? null;
    $unidadesDiscretas = \App\Models\Expense::unidadesDiscretas();
    $unidadeAtual = old('unidade', $E?->unidade);
    $quantidadeAtual = old('quantidade', $E?->quantidade ?? 1);
@endphp

<div class="border-b border-leaf-100 pb-5 mb-6">
    <h2 class="text-base font-bold text-leaf-900">Lançamento</h2>
    <p class="text-sm text-leaf-500 mt-0.5">Registro do gasto. Você pode informar quantidade e valor unitário, ou só o valor total.</p>
</div>

<div class="space-y-5 mb-6">
    <div class="grid sm:grid-cols-2 gap-5">
        <div>
            <label for="data" class="block text-sm font-bold text-leaf-900 mb-2">
                Data <span class="text-rose-500">*</span>
            </label>
            <input id="data" type="date" name="data" required
                   value="{{ old('data', $E ? $E->data->format('Y-m-d') : now()->format('Y-m-d')) }}"
                   class="w-full px-4 py-3 text-base rounded-lg border border-leaf-200 focus:border-leaf-500 focus:ring-4 focus:ring-leaf-500/15 outline-none transition">
            <p class="mt-1.5 text-sm text-leaf-500">Quando o gasto foi efetivamente realizado.</p>
            @error('data')<p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="expense_category_id" class="block text-sm font-bold text-leaf-900 mb-2">
                Categoria <span class="text-rose-500">*</span>
            </label>
            <select id="expense_category_id" name="expense_category_id" required
                    class="w-full px-4 py-3 text-base rounded-lg border border-leaf-200 focus:border-leaf-500 focus:ring-4 focus:ring-leaf-500/15 outline-none transition bg-white">
                <option value="">— selecione —</option>
                @foreach($categories as $c)
                    <option value="{{ $c->id }}" @selected(old('expense_category_id', $E?->expense_category_id) == $c->id)>{{ $c->nome }}</option>
                @endforeach
            </select>
            <p class="mt-1.5 text-sm text-leaf-500">
                Não viu? <a href="{{ route('despesas.categorias.create') }}" class="text-leaf-700 font-semibold hover:underline">Cadastrar nova</a>.
            </p>
            @error('expense_category_id')<p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>@enderror
        </div>
    </div>

    <div>
        <label for="descricao" class="block text-sm font-bold text-leaf-900 mb-2">
            Descrição <span class="text-rose-500">*</span>
        </label>
        <input id="descricao" type="text" name="descricao" required maxlength="200"
               value="{{ old('descricao', $E?->descricao) }}"
               placeholder="ex: Diesel para o secador, Pagamento da Maria, IPVA do trator…"
               class="w-full px-4 py-3 text-base rounded-lg border border-leaf-200 placeholder-leaf-300 focus:border-leaf-500 focus:ring-4 focus:ring-leaf-500/15 outline-none transition">
        <p class="mt-1.5 text-sm text-leaf-500">O que foi este gasto. Aparece na listagem e nos relatórios.</p>
        @error('descricao')<p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>@enderror
    </div>
</div>

<div class="border-b border-leaf-100 pb-5 mb-6">
    <h2 class="text-base font-bold text-leaf-900">Valores</h2>
    <p class="text-sm text-leaf-500 mt-0.5">
        Se for compra por unidade, preencha quantidade e valor unitário — o total é calculado.
        Se for valor único (ex: imposto), preencha só o <strong>Valor total</strong>.
    </p>
</div>

<div class="grid grid-cols-1 sm:grid-cols-12 gap-4 mb-6"
     x-data="{
         unidade: @js($unidadeAtual),
         discretas: @js($unidadesDiscretas),
         get isDiscrete() { return this.unidade && this.discretas.includes(this.unidade); }
     }">

    <div class="sm:col-span-3">
        <label for="unidade" class="block text-sm font-bold text-leaf-900 mb-2">Unidade</label>
        <select id="unidade" name="unidade" x-model="unidade"
                class="w-full px-4 py-3 text-base rounded-lg border border-leaf-200 focus:border-leaf-500 focus:ring-4 focus:ring-leaf-500/15 outline-none transition bg-white">
            <option value="">— sem unidade —</option>
            @foreach(\App\Models\Expense::UNIDADES as $sigla => $u)
                <option value="{{ $sigla }}" @selected($unidadeAtual === $sigla)>{{ $sigla }} — {{ $u['label'] }}{{ $u['discreta'] ? ' (inteiro)' : '' }}</option>
            @endforeach
        </select>
        <p class="mt-1.5 text-xs text-leaf-500">Selecione se aplicável</p>
    </div>

    <div class="sm:col-span-3">
        <label for="quantidade" class="block text-sm font-bold text-leaf-900 mb-2">Quantidade</label>
        <input id="quantidade" type="number" inputmode="decimal" name="quantidade"
               x-bind:step="isDiscrete ? '1' : '0.001'"
               x-bind:min="isDiscrete ? '1' : '0.001'"
               x-bind:inputmode="isDiscrete ? 'numeric' : 'decimal'"
               x-on:input="if (isDiscrete) $el.value = $el.value.replace(/[.,]/g, '').replace(/\D/g, '')"
               value="{{ $quantidadeAtual }}"
               placeholder="1"
               class="w-full px-4 py-3 text-base rounded-lg border border-leaf-200 placeholder-leaf-300 focus:border-leaf-500 focus:ring-4 focus:ring-leaf-500/15 outline-none transition">
        <p class="mt-1.5 text-xs text-leaf-500">
            <span x-show="isDiscrete">Apenas números inteiros (ex: 5)</span>
            <span x-show="!isDiscrete && unidade">Decimais permitidos (ex: 2,5)</span>
            <span x-show="!unidade">Quanto comprou</span>
        </p>
        @error('quantidade')<p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div class="sm:col-span-3">
        <label for="valor_unitario" class="block text-sm font-bold text-leaf-900 mb-2">Vlr. unitário</label>
        <div class="relative">
            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-sm font-semibold text-leaf-500 pointer-events-none">R$</span>
            <input id="valor_unitario" type="number" step="0.01" min="0" inputmode="decimal" name="valor_unitario"
                   value="{{ old('valor_unitario', $E?->valor_unitario ?? 0) }}"
                   placeholder="0,00"
                   class="w-full pl-12 pr-4 py-3 text-base rounded-lg border border-leaf-200 placeholder-leaf-300 focus:border-leaf-500 focus:ring-4 focus:ring-leaf-500/15 outline-none transition">
        </div>
        <p class="mt-1.5 text-xs text-leaf-500">Por unidade</p>
    </div>

    <div class="sm:col-span-3">
        <label for="valor_total" class="block text-sm font-bold text-leaf-900 mb-2">
            Vlr. total <span class="text-rose-500">*</span>
        </label>
        <div class="relative">
            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-sm font-semibold text-leaf-500 pointer-events-none">R$</span>
            <input id="valor_total" type="number" step="0.01" min="0.01" inputmode="decimal" name="valor_total" required
                   value="{{ old('valor_total', $E?->valor_total) }}"
                   placeholder="0,00"
                   class="w-full pl-12 pr-4 py-3 text-base rounded-lg border-2 border-leaf-300 placeholder-leaf-300 focus:border-leaf-500 focus:ring-4 focus:ring-leaf-500/15 outline-none transition font-semibold">
        </div>
        <p class="mt-1.5 text-xs text-leaf-500">Total pago</p>
        @error('valor_total')<p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>@enderror
    </div>
</div>

<div class="mb-6">
    <label for="observacoes" class="block text-sm font-bold text-leaf-900 mb-2">Observações</label>
    <textarea id="observacoes" name="observacoes" rows="3"
              placeholder="Forma de pagamento, fornecedor, número da nota…"
              class="w-full px-4 py-3 text-base rounded-lg border border-leaf-200 placeholder-leaf-300 focus:border-leaf-500 focus:ring-4 focus:ring-leaf-500/15 outline-none transition">{{ old('observacoes', $E?->observacoes) }}</textarea>
    <p class="mt-1.5 text-sm text-leaf-500">Opcional. Útil pra encontrar a despesa depois.</p>
</div>
