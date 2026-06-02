@extends('layouts.app')

@section('title', 'Comprar café')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-6">
        <p class="text-xs text-leaf-500 mb-1">
            <a href="{{ route('compras.index') }}" class="hover:underline">Compras de café</a> · <span class="text-leaf-700">Nova</span>
        </p>
        <h1 class="text-2xl font-bold text-leaf-900">Nova compra de café</h1>
        <p class="text-sm text-leaf-500 mt-1">A compra cria uma despesa no caixa e adiciona o café ao estoque da fazenda.</p>
    </div>

    <form method="POST" action="{{ route('compras.store') }}" class="bg-white rounded-2xl border border-leaf-100 shadow-sm p-6 sm:p-8"
          x-data="{
              produto: @js(old('produto', 'coco')),
              qtde: @js(old('quantidade_kg', '')),
              unit: @js(old('valor_unitario', '')),
              total: @js(old('valor_total', '')),
              recalcTotal() {
                  if (this.qtde !== '' && this.unit !== '') {
                      this.total = (parseFloat(this.qtde) * parseFloat(this.unit)).toFixed(2);
                  }
              },
              recalcUnit() {
                  if (this.qtde !== '' && this.total !== '' && parseFloat(this.qtde) > 0) {
                      this.unit = (parseFloat(this.total) / parseFloat(this.qtde)).toFixed(2);
                  }
              }
          }">
        @csrf

        <div class="space-y-6">
            <div class="grid sm:grid-cols-2 gap-5">
                <div>
                    <label for="data" class="block text-sm font-bold text-leaf-900 mb-2">Data da compra <span class="text-rose-500">*</span></label>
                    <input id="data" type="date" name="data" required
                           value="{{ old('data', now()->format('Y-m-d')) }}"
                           max="{{ now()->format('Y-m-d') }}"
                           class="w-full px-4 py-3 text-base rounded-lg border border-leaf-200 focus:border-leaf-500 focus:ring-4 focus:ring-leaf-500/15 outline-none transition">
                    @error('data')<p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="fornecedor" class="block text-sm font-bold text-leaf-900 mb-2">Fornecedor</label>
                    <input id="fornecedor" type="text" name="fornecedor" maxlength="120"
                           value="{{ old('fornecedor') }}"
                           placeholder="Quem vendeu o café"
                           class="w-full px-4 py-3 text-base rounded-lg border border-leaf-200 placeholder-leaf-300 focus:border-leaf-500 focus:ring-4 focus:ring-leaf-500/15 outline-none transition">
                    @error('fornecedor')<p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-bold text-leaf-900 mb-2">Produto comprado <span class="text-rose-500">*</span></label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="flex items-center gap-3 p-4 rounded-lg border-2 cursor-pointer transition"
                           :class="produto === 'coco' ? 'border-amber-500 bg-amber-50' : 'border-leaf-200 hover:border-leaf-300'">
                        <input type="radio" name="produto" value="coco" x-model="produto" class="w-4 h-4 text-amber-600 focus:ring-amber-500">
                        <div>
                            <p class="text-sm font-bold text-leaf-900">Café côco</p>
                            <p class="text-xs text-leaf-500">Cru, ainda pra secar</p>
                        </div>
                    </label>
                    <label class="flex items-center gap-3 p-4 rounded-lg border-2 cursor-pointer transition"
                           :class="produto === 'seco' ? 'border-emerald-500 bg-emerald-50' : 'border-leaf-200 hover:border-leaf-300'">
                        <input type="radio" name="produto" value="seco" x-model="produto" class="w-4 h-4 text-emerald-600 focus:ring-emerald-500">
                        <div>
                            <p class="text-sm font-bold text-leaf-900">Café seco</p>
                            <p class="text-xs text-leaf-500">Pronto pra venda</p>
                        </div>
                    </label>
                </div>
                @error('produto')<p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="quantidade_kg" class="block text-sm font-bold text-leaf-900 mb-2">Quantidade comprada <span class="text-rose-500">*</span></label>
                <x-input-quantidade name="quantidade_kg" id="quantidade_kg" :value="old('quantidade_kg')" required
                                    x-on:input.debounce.250ms="qtde = $event.target.value; recalcTotal()" />
                @error('quantidade_kg')<p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>@enderror
                <p class="mt-1.5 text-xs text-leaf-500">Você pode digitar em kg ou em sacos (1 sc = 60 kg) — atualiza sozinho.</p>
            </div>

            <div class="grid sm:grid-cols-2 gap-5">
                <div>
                    <label for="valor_unitario" class="block text-sm font-bold text-leaf-900 mb-2">Valor por kg</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-sm font-semibold text-leaf-500 pointer-events-none">R$</span>
                        <input id="valor_unitario" type="number" step="0.01" min="0" inputmode="decimal" name="valor_unitario"
                               x-model="unit" x-on:input.debounce.250ms="recalcTotal()"
                               placeholder="0,00"
                               class="w-full pl-12 pr-4 py-3 text-base rounded-lg border border-leaf-200 placeholder-leaf-300 focus:border-leaf-500 focus:ring-4 focus:ring-leaf-500/15 outline-none transition">
                    </div>
                    <p class="mt-1.5 text-xs text-leaf-500">Opcional. Multiplica pelo total.</p>
                    @error('valor_unitario')<p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="valor_total" class="block text-sm font-bold text-leaf-900 mb-2">Valor total pago <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-sm font-semibold text-leaf-500 pointer-events-none">R$</span>
                        <input id="valor_total" type="number" step="0.01" min="0.01" inputmode="decimal" name="valor_total" required
                               x-model="total" x-on:input.debounce.250ms="recalcUnit()"
                               placeholder="0,00"
                               class="w-full pl-12 pr-4 py-3 text-base rounded-lg border-2 border-leaf-300 placeholder-leaf-300 focus:border-leaf-500 focus:ring-4 focus:ring-leaf-500/15 outline-none transition font-semibold">
                    </div>
                    @error('valor_total')<p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label for="observacoes" class="block text-sm font-bold text-leaf-900 mb-2">Observações</label>
                <textarea id="observacoes" name="observacoes" rows="3" maxlength="500"
                          placeholder="Forma de pagamento, qualidade do café, contato do fornecedor…"
                          class="w-full px-4 py-3 text-base rounded-lg border border-leaf-200 placeholder-leaf-300 focus:border-leaf-500 focus:ring-4 focus:ring-leaf-500/15 outline-none transition">{{ old('observacoes') }}</textarea>
                @error('observacoes')<p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="flex flex-col-reverse sm:flex-row sm:items-center gap-3 pt-6 mt-6 border-t border-leaf-100">
            <button type="submit" class="px-6 py-3 text-base font-bold text-white bg-leaf-700 hover:bg-leaf-800 rounded-lg transition shadow-sm w-full sm:w-auto">
                Registrar compra
            </button>
            <a href="{{ route('compras.index') }}" class="text-center sm:text-left px-4 py-3 sm:py-0 text-sm font-semibold text-leaf-600 hover:text-leaf-900">Cancelar</a>
        </div>
    </form>
</div>
@endsection
