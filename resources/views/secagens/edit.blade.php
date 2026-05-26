@extends('layouts.app')

@section('title', 'Secagem #'.$secagem->numero)

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="flex items-start justify-between mb-6 gap-4 flex-wrap">
        <div>
            <p class="text-xs text-leaf-500 mb-1">
                <a href="{{ route('secagens.index') }}" class="hover:underline">Secagens</a> · <span class="text-leaf-700">#{{ $secagem->numero }}</span>
            </p>
            <h1 class="text-2xl font-bold text-leaf-900">Secagem #{{ $secagem->numero }}</h1>
            <p class="text-sm text-leaf-500 mt-0.5">
                {{ $secagem->data->format('d/m/Y') }} · {{ $secagem->secadorNome() }} ·
                <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-100 text-amber-700">RASCUNHO</span>
            </p>
        </div>
    </div>

    <div class="grid lg:grid-cols-2 gap-5 mb-6">
        {{-- Dados gerais --}}
        <form method="POST" action="{{ route('secagens.update', $secagem) }}" class="bg-white rounded-2xl border border-leaf-100 shadow-sm p-6">
            @csrf @method('PUT')
            <div class="border-b border-leaf-100 pb-4 mb-5">
                <h2 class="text-base font-bold text-leaf-900">Dados da secagem</h2>
                <p class="text-sm text-leaf-500 mt-0.5">Você pode editar enquanto for rascunho.</p>
            </div>

            <div class="space-y-4">
                <div>
                    <label for="data" class="block text-sm font-bold text-leaf-900 mb-2">
                        Data <span class="text-rose-500">*</span>
                    </label>
                    <input id="data" type="date" name="data" value="{{ old('data', $secagem->data->format('Y-m-d')) }}" required
                           class="w-full px-4 py-3 text-base rounded-lg border border-leaf-200 focus:border-leaf-500 focus:ring-4 focus:ring-leaf-500/15 outline-none transition">
                </div>
                <div>
                    <label for="dryer_id" class="block text-sm font-bold text-leaf-900 mb-2">
                        Secador <span class="text-rose-500">*</span>
                    </label>
                    <select id="dryer_id" name="dryer_id" required
                            class="w-full px-4 py-3 text-base rounded-lg border border-leaf-200 focus:border-leaf-500 focus:ring-4 focus:ring-leaf-500/15 outline-none transition bg-white">
                        @foreach($dryers as $d)
                            <option value="{{ $d['id'] }}" @selected(old('dryer_id', $secagem->dryer_id) == $d['id'])>{{ $d['nome'] }}</option>
                        @endforeach
                    </select>
                    @error('dryer_id')<p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="observacoes" class="block text-sm font-bold text-leaf-900 mb-2">Observações</label>
                    <textarea id="observacoes" name="observacoes" rows="3"
                              placeholder="Tempo de secagem, particularidades…"
                              class="w-full px-4 py-3 text-base rounded-lg border border-leaf-200 placeholder-leaf-300 focus:border-leaf-500 focus:ring-4 focus:ring-leaf-500/15 outline-none transition">{{ old('observacoes', $secagem->observacoes) }}</textarea>
                </div>
            </div>

            <button class="mt-5 w-full sm:w-auto px-5 py-2.5 text-sm font-bold text-white bg-leaf-700 hover:bg-leaf-800 rounded-lg transition">Salvar dados</button>
        </form>

        {{-- Adicionar lote (entrada do secador) --}}
        <form method="POST" action="{{ route('secagens.items.store', $secagem) }}"
              class="bg-white rounded-2xl border border-leaf-100 shadow-sm p-6"
              x-data="{ originType: '{{ old('origin_type', 'cliente') }}' }">
            @csrf
            <div class="border-b border-leaf-100 pb-4 mb-5">
                <h2 class="text-base font-bold text-leaf-900">Lançar entrada no secador</h2>
                <p class="text-sm text-leaf-500 mt-0.5">Pesou e colocou o café no secador? Registra aqui. A <strong>saída</strong> (seco + comissão) é lançada depois, quando o ciclo terminar.</p>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-bold text-leaf-900 mb-2">Origem do café</label>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="flex items-center justify-center gap-2 px-3 py-2.5 rounded-lg border-2 cursor-pointer transition"
                               :class="originType === 'cliente' ? 'border-leaf-700 bg-leaf-50 text-leaf-900 font-semibold' : 'border-leaf-200 text-leaf-600'">
                            <input type="radio" name="origin_type" value="cliente" x-model="originType" class="hidden">
                            <span>🧑 Cliente</span>
                        </label>
                        <label class="flex items-center justify-center gap-2 px-3 py-2.5 rounded-lg border-2 cursor-pointer transition"
                               :class="originType === 'area' ? 'border-leaf-700 bg-leaf-50 text-leaf-900 font-semibold' : 'border-leaf-200 text-leaf-600'">
                            <input type="radio" name="origin_type" value="area" x-model="originType" class="hidden">
                            <span>🌱 Área própria</span>
                        </label>
                    </div>
                </div>

                <div x-show="originType === 'cliente'" x-cloak>
                    <label for="origin_id_cliente" class="block text-sm font-bold text-leaf-900 mb-2">Cliente <span class="text-rose-500">*</span></label>
                    <select id="origin_id_cliente" name="origin_id" x-bind:required="originType === 'cliente'" x-bind:disabled="originType !== 'cliente'"
                            class="w-full px-4 py-3 text-base rounded-lg border border-leaf-200 focus:border-leaf-500 focus:ring-4 focus:ring-leaf-500/15 outline-none transition bg-white">
                        <option value="">Selecione</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}">{{ $c->nome }} ({{ number_format($c->saldo_coco_kg, 2, ',', '.') }} kg côco)</option>
                        @endforeach
                    </select>
                </div>

                <div x-show="originType === 'area'" x-cloak>
                    <label for="origin_id_area" class="block text-sm font-bold text-leaf-900 mb-2">Área <span class="text-rose-500">*</span></label>
                    <select id="origin_id_area" name="origin_id" x-bind:required="originType === 'area'" x-bind:disabled="originType !== 'area'"
                            class="w-full px-4 py-3 text-base rounded-lg border border-leaf-200 focus:border-leaf-500 focus:ring-4 focus:ring-leaf-500/15 outline-none transition bg-white">
                        <option value="">Selecione</option>
                        @foreach($areas as $a)
                            <option value="{{ $a->id }}">{{ $a->nome }}</option>
                        @endforeach
                    </select>
                    <p class="mt-2 text-xs text-leaf-500">Sem saldo de côco? Registre uma <a href="{{ route('colheitas.create') }}" class="text-leaf-700 font-semibold hover:underline">colheita</a> primeiro.</p>
                </div>

                @error('origin_id')<p class="text-sm font-medium text-rose-600">{{ $message }}</p>@enderror

                <div>
                    <label for="qtd_recebida" class="block text-sm font-bold text-leaf-900 mb-2">Quantidade recebida (côco)</label>
                    <div class="relative">
                        <input id="qtd_recebida" type="number" step="0.01" min="0.01" inputmode="decimal" name="quantidade_recebida_kg" required
                               placeholder="0,00"
                               class="w-full pl-3 pr-10 py-3 text-base rounded-lg border border-leaf-200 placeholder-leaf-300 focus:border-leaf-500 focus:ring-4 focus:ring-leaf-500/15 outline-none transition">
                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-semibold text-leaf-500 pointer-events-none">kg</span>
                    </div>
                    @error('quantidade_recebida_kg')<p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <button class="mt-5 w-full sm:w-auto px-5 py-2.5 text-sm font-bold text-white bg-leaf-600 hover:bg-leaf-700 rounded-lg transition">+ Lançar entrada</button>
        </form>
    </div>

    {{-- Lista de items --}}
    <div class="bg-white rounded-2xl border border-leaf-100 shadow-sm overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-leaf-100 flex items-center justify-between">
            <h2 class="text-sm font-bold text-leaf-900 uppercase tracking-wider">Itens da secagem</h2>
            <span class="text-xs text-leaf-500">{{ $secagem->items->count() }} item(ns)</span>
        </div>

        <ul class="divide-y divide-leaf-100">
            @forelse($secagem->items as $item)
                <li class="px-4 sm:px-6 py-4">
                    <div class="flex items-start justify-between gap-3 mb-3">
                        <div>
                            <p class="font-semibold text-leaf-900 flex items-center gap-2">
                                <span>{{ $item->isArea() ? '🌱' : '🧑' }}</span>
                                {{ $item->originLabel() }}
                                @if($item->hasSaida())
                                    <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 text-emerald-700">SAÍDA REGISTRADA</span>
                                @else
                                    <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-100 text-amber-700">AGUARDANDO SAÍDA</span>
                                @endif
                            </p>
                            <p class="text-xs text-leaf-500 mt-0.5">
                                Recebido: {{ number_format($item->quantidade_recebida_kg, 2, ',', '.') }} kg ({{ \App\Support\Sacos::formatSacos($item->quantidade_recebida_kg) }})
                            </p>
                        </div>
                        <form method="POST" action="{{ route('secagens.items.destroy', [$secagem, $item]) }}"
                              data-confirm="Remover este item da secagem?"
                              data-confirm-text="O lote sai da lista. Pode ser adicionado de novo enquanto a secagem for rascunho."
                              data-confirm-yes="Sim, remover">
                            @csrf @method('DELETE')
                            <button class="inline-flex items-center gap-1 text-rose-600 text-xs font-semibold hover:underline">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                remover
                            </button>
                        </form>
                    </div>

                    @if(! $item->hasSaida())
                        {{-- Form de registrar saída inline --}}
                        <form method="POST" action="{{ route('secagens.items.saida', [$secagem, $item]) }}"
                              class="mt-3 grid grid-cols-1 sm:grid-cols-3 gap-3 bg-leaf-50/40 p-3 rounded-lg">
                            @csrf @method('PATCH')
                            <div>
                                <label class="block text-xs font-semibold text-leaf-700 mb-1">Quantidade seca</label>
                                <div class="relative">
                                    <input type="number" step="0.01" min="0.01" inputmode="decimal" name="quantidade_seca_kg" required
                                           placeholder="0,00"
                                           class="w-full pl-3 pr-10 py-2 text-sm rounded-md border border-leaf-200 focus:border-leaf-500 focus:ring-2 focus:ring-leaf-500/15 outline-none">
                                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-semibold text-leaf-500">kg</span>
                                </div>
                            </div>
                            @if($item->isCustomer())
                                <div>
                                    <label class="block text-xs font-semibold text-leaf-700 mb-1">Comissão</label>
                                    <div class="relative">
                                        <input type="number" step="0.01" min="0" max="100" inputmode="decimal" name="comissao_percentual" value="0"
                                               placeholder="ex: 10"
                                               class="w-full pl-3 pr-8 py-2 text-sm rounded-md border border-leaf-200 focus:border-leaf-500 focus:ring-2 focus:ring-leaf-500/15 outline-none">
                                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-semibold text-leaf-500">%</span>
                                    </div>
                                </div>
                            @else
                                <div class="flex items-center text-xs text-leaf-500 pt-5">
                                    Sem comissão (café próprio).
                                </div>
                            @endif
                            <div class="flex items-end">
                                <button class="w-full px-4 py-2 text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-md transition">Registrar saída</button>
                            </div>
                        </form>
                    @else
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-xs text-leaf-700 mt-2">
                            <div><p class="text-[10px] text-leaf-500 uppercase">Seco</p><p class="font-semibold">{{ number_format($item->quantidade_seca_kg, 2, ',', '.') }} kg</p></div>
                            <div><p class="text-[10px] text-leaf-500 uppercase">Rendimento</p><p class="font-semibold">{{ number_format($item->rendimentoPercentual(), 2, ',', '.') }}% · {{ number_format($item->proporcaoCocoSeco(), 2, ',', '.') }} sc côco/sc seco</p></div>
                            @if($item->isCustomer())
                                <div><p class="text-[10px] text-leaf-500 uppercase">Comissão</p><p class="font-semibold">{{ number_format($item->comissao_kg, 2, ',', '.') }} kg ({{ number_format($item->comissao_percentual, 2, ',', '.') }}%)</p></div>
                            @endif
                            <div><p class="text-[10px] text-leaf-500 uppercase">Líquido</p><p class="font-bold text-emerald-700">{{ number_format($item->saldo_liquido_kg, 2, ',', '.') }} kg</p></div>
                        </div>
                    @endif
                </li>
            @empty
                <li class="px-4 py-12 text-center text-leaf-500">Adicione lotes usando o formulário acima.</li>
            @endforelse
            @if($secagem->items->isNotEmpty())
                <li class="px-4 py-3 bg-leaf-50/50">
                    <p class="text-[10px] uppercase tracking-wider text-leaf-500 mb-1 font-semibold">Totais</p>
                    <div class="grid grid-cols-3 gap-2 text-sm">
                        <div>
                            <p class="text-[10px] text-leaf-500">Recebido</p>
                            <p class="font-bold text-leaf-900">{{ number_format($secagem->totalRecebidoKg(), 2, ',', '.') }} kg</p>
                        </div>
                        <div>
                            <p class="text-[10px] text-leaf-500">Seco</p>
                            <p class="font-bold text-leaf-900">{{ number_format($secagem->totalSecoKg(), 2, ',', '.') }} kg</p>
                        </div>
                        <div>
                            <p class="text-[10px] text-leaf-500">Comissão</p>
                            <p class="font-bold text-leaf-900">{{ number_format($secagem->totalComissaoKg(), 2, ',', '.') }} kg</p>
                        </div>
                    </div>
                </li>
            @endif
        </ul>
    </div>

    {{-- Ações finais --}}
    <div class="bg-white rounded-2xl border border-leaf-100 shadow-sm p-6">
        <div class="flex flex-col sm:flex-row sm:items-center gap-3">
            @can('conclude', $secagem)
                <form method="POST" action="{{ route('secagens.conclude', $secagem) }}"
                      data-confirm="Concluir esta secagem?"
                      data-confirm-text="Os saldos serão atualizados: côco debita, seco credita, comissão vai pra fazenda. Depois disso só dá pra ajustar via 'reabrir'."
                      data-confirm-icon="question"
                      data-confirm-yes="Sim, concluir"
                      data-confirm-danger="0"
                      class="w-full sm:w-auto">
                    @csrf
                    <button class="w-full sm:w-auto px-6 py-3 text-base font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition shadow-sm" {{ ! $secagem->todosItemsTemSaida() ? 'disabled' : '' }}>
                        ✓ Concluir secagem
                    </button>
                </form>
                @unless($secagem->todosItemsTemSaida())
                    <p class="text-xs text-amber-700">Registre a saída de todos os itens antes de concluir.</p>
                @endunless
            @endcan
            @can('delete', $secagem)
                <span class="hidden sm:flex flex-1"></span>
                <form method="POST" action="{{ route('secagens.destroy', $secagem) }}"
                      data-confirm="Excluir este rascunho?"
                      data-confirm-text="Os itens adicionados serão perdidos. Os saldos não foram debitados ainda, nada precisa ser revertido."
                      data-confirm-yes="Sim, excluir"
                      class="w-full sm:w-auto">
                    @csrf @method('DELETE')
                    <button class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-3 text-sm font-semibold text-rose-600 hover:bg-rose-50 rounded-lg transition border border-transparent hover:border-rose-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        Excluir rascunho
                    </button>
                </form>
            @endcan
        </div>
    </div>
</div>
@endsection
