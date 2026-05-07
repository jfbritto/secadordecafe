@extends('layouts.app')

@section('title', 'Secagem #'.$secagem->numero)

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="flex items-start justify-between mb-6 gap-4 flex-wrap">
        <div>
            <p class="text-xs text-coffee-500 mb-1">
                <a href="{{ route('secagens.index') }}" class="hover:underline">Secagens</a> · <span class="text-coffee-700">#{{ $secagem->numero }}</span>
            </p>
            <h1 class="text-2xl font-bold text-coffee-900">Secagem #{{ $secagem->numero }}</h1>
            <p class="text-sm text-coffee-500 mt-0.5">
                {{ $secagem->data->format('d/m/Y') }} · {{ $secagem->secadorNome() }} ·
                <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-100 text-amber-700">RASCUNHO</span>
            </p>
        </div>
    </div>

    <div class="grid lg:grid-cols-2 gap-5 mb-6">
        {{-- Dados gerais --}}
        <form method="POST" action="{{ route('secagens.update', $secagem) }}" class="bg-white rounded-2xl border border-coffee-100 shadow-sm p-6">
            @csrf @method('PUT')
            <div class="border-b border-coffee-100 pb-4 mb-5">
                <h2 class="text-base font-bold text-coffee-900">Dados da secagem</h2>
                <p class="text-sm text-coffee-500 mt-0.5">Você pode editar enquanto for rascunho.</p>
            </div>

            <div class="space-y-4">
                <div>
                    <label for="data" class="block text-sm font-bold text-coffee-900 mb-2">
                        Data <span class="text-rose-500">*</span>
                    </label>
                    <input id="data" type="date" name="data" value="{{ old('data', $secagem->data->format('Y-m-d')) }}" required
                           class="w-full px-4 py-3 text-base rounded-lg border border-coffee-200 focus:border-coffee-500 focus:ring-4 focus:ring-coffee-500/15 outline-none transition">
                </div>
                <div>
                    <label for="dryer_id" class="block text-sm font-bold text-coffee-900 mb-2">
                        Secador <span class="text-rose-500">*</span>
                    </label>
                    <select id="dryer_id" name="dryer_id" required
                            class="w-full px-4 py-3 text-base rounded-lg border border-coffee-200 focus:border-coffee-500 focus:ring-4 focus:ring-coffee-500/15 outline-none transition bg-white">
                        @foreach($dryers as $d)
                            <option value="{{ $d->id }}" @selected(old('dryer_id', $secagem->dryer_id) == $d->id)>{{ $d->nome }}</option>
                        @endforeach
                    </select>
                    @error('dryer_id')<p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="observacoes" class="block text-sm font-bold text-coffee-900 mb-2">Observações</label>
                    <textarea id="observacoes" name="observacoes" rows="3"
                              placeholder="Tempo de secagem, particularidades…"
                              class="w-full px-4 py-3 text-base rounded-lg border border-coffee-200 placeholder-coffee-300 focus:border-coffee-500 focus:ring-4 focus:ring-coffee-500/15 outline-none transition">{{ old('observacoes', $secagem->observacoes) }}</textarea>
                </div>
            </div>

            <button class="mt-5 w-full sm:w-auto px-5 py-2.5 text-sm font-bold text-white bg-coffee-700 hover:bg-coffee-800 rounded-lg transition">Salvar dados</button>
        </form>

        {{-- Adicionar cliente --}}
        <form method="POST" action="{{ route('secagens.items.store', $secagem) }}" class="bg-white rounded-2xl border border-coffee-100 shadow-sm p-6">
            @csrf
            <div class="border-b border-coffee-100 pb-4 mb-5">
                <h2 class="text-base font-bold text-coffee-900">Adicionar cliente à secagem</h2>
                <p class="text-sm text-coffee-500 mt-0.5">Cada item representa um produtor com sua quantidade própria.</p>
            </div>

            <div class="space-y-4">
                <div>
                    <label for="customer_id" class="block text-sm font-bold text-coffee-900 mb-2">
                        Cliente <span class="text-rose-500">*</span>
                    </label>
                    <select id="customer_id" name="customer_id" required
                            class="w-full px-4 py-3 text-base rounded-lg border border-coffee-200 focus:border-coffee-500 focus:ring-4 focus:ring-coffee-500/15 outline-none transition bg-white">
                        <option value="">— selecione —</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}">{{ $c->nome }} (saldo {{ number_format($c->saldo_cafe_kg, 3, ',', '.') }} kg)</option>
                        @endforeach
                    </select>
                    @error('customer_id')<p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label for="qtd_recebida" class="block text-sm font-bold text-coffee-900 mb-2">Recebido</label>
                        <div class="relative">
                            <input id="qtd_recebida" type="number" step="0.001" min="0.001" inputmode="decimal" name="quantidade_recebida_kg" required
                                   placeholder="0,000"
                                   class="w-full pl-3 pr-10 py-3 text-base rounded-lg border border-coffee-200 placeholder-coffee-300 focus:border-coffee-500 focus:ring-4 focus:ring-coffee-500/15 outline-none transition">
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-semibold text-coffee-500 pointer-events-none">kg</span>
                        </div>
                        <p class="mt-1 text-xs text-coffee-500">Café côco</p>
                    </div>
                    <div>
                        <label for="qtd_seca" class="block text-sm font-bold text-coffee-900 mb-2">Seco</label>
                        <div class="relative">
                            <input id="qtd_seca" type="number" step="0.001" min="0.001" inputmode="decimal" name="quantidade_seca_kg" required
                                   placeholder="0,000"
                                   class="w-full pl-3 pr-10 py-3 text-base rounded-lg border border-coffee-200 placeholder-coffee-300 focus:border-coffee-500 focus:ring-4 focus:ring-coffee-500/15 outline-none transition">
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-semibold text-coffee-500 pointer-events-none">kg</span>
                        </div>
                        <p class="mt-1 text-xs text-coffee-500">Pilado</p>
                    </div>
                    <div>
                        <label for="comissao_pct" class="block text-sm font-bold text-coffee-900 mb-2">Comissão</label>
                        <div class="relative">
                            <input id="comissao_pct" type="number" step="0.01" min="0" max="100" inputmode="decimal" name="comissao_percentual" value="0"
                                   class="w-full pl-3 pr-8 py-3 text-base rounded-lg border border-coffee-200 placeholder-coffee-300 focus:border-coffee-500 focus:ring-4 focus:ring-coffee-500/15 outline-none transition">
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-sm font-semibold text-coffee-500 pointer-events-none">%</span>
                        </div>
                        <p class="mt-1 text-xs text-coffee-500">Sobre o seco</p>
                    </div>
                </div>
                @error('quantidade_recebida_kg')<p class="text-sm font-medium text-rose-600">{{ $message }}</p>@enderror
                @error('quantidade_seca_kg')<p class="text-sm font-medium text-rose-600">{{ $message }}</p>@enderror
            </div>

            <button class="mt-5 w-full sm:w-auto px-5 py-2.5 text-sm font-bold text-white bg-coffee-600 hover:bg-coffee-700 rounded-lg transition">+ Adicionar item</button>
        </form>
    </div>

    {{-- Tabela de items --}}
    <div class="bg-white rounded-2xl border border-coffee-100 shadow-sm overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-coffee-100 flex items-center justify-between">
            <h2 class="text-sm font-bold text-coffee-900 uppercase tracking-wider">Itens da secagem</h2>
            <span class="text-xs text-coffee-500">{{ $secagem->items->count() }} item(ns)</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-coffee-50/50 text-coffee-600 text-xs uppercase tracking-wider">
                    <tr>
                        <th class="text-left px-4 py-3 font-semibold">Cliente</th>
                        <th class="text-right px-4 py-3 font-semibold">Recebido</th>
                        <th class="text-right px-4 py-3 font-semibold">Seco</th>
                        <th class="text-right px-4 py-3 font-semibold">Rendim.</th>
                        <th class="text-right px-4 py-3 font-semibold">Comissão</th>
                        <th class="text-right px-4 py-3 font-semibold">Líquido</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-coffee-100">
                    @forelse($secagem->items as $item)
                        <tr>
                            <td class="px-4 py-3 text-coffee-900 font-medium">{{ $item->customer->nome }}</td>
                            <td class="px-4 py-3 text-right text-coffee-700">{{ number_format($item->quantidade_recebida_kg, 3, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right text-coffee-700">{{ number_format($item->quantidade_seca_kg, 3, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right text-coffee-700">{{ number_format($item->rendimentoPercentual(), 2, ',', '.') }}%</td>
                            <td class="px-4 py-3 text-right text-coffee-700">{{ number_format($item->comissao_kg, 3, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right font-bold text-coffee-800">{{ number_format($item->saldo_liquido_kg, 3, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right">
                                <form method="POST" action="{{ route('secagens.items.destroy', [$secagem, $item]) }}"
                                      data-confirm="Remover este item da secagem?"
                                      data-confirm-text="O cliente sai da lista. Você pode adicionar de novo enquanto a secagem for rascunho."
                                      data-confirm-yes="Sim, remover" class="inline">
                                    @csrf @method('DELETE')
                                    <button class="inline-flex items-center gap-1 text-rose-600 text-xs font-semibold hover:underline">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        remover
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-6 py-12 text-center text-coffee-500">
                            Adicione clientes usando o formulário acima.
                        </td></tr>
                    @endforelse
                </tbody>
                @if($secagem->items->isNotEmpty())
                    <tfoot class="bg-coffee-50/50 font-bold text-coffee-900">
                        <tr>
                            <td class="px-4 py-3">Totais</td>
                            <td class="px-4 py-3 text-right">{{ number_format($secagem->totalRecebidoKg(), 3, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right">{{ number_format($secagem->totalSecoKg(), 3, ',', '.') }}</td>
                            <td></td>
                            <td class="px-4 py-3 text-right">{{ number_format($secagem->totalComissaoKg(), 3, ',', '.') }}</td>
                            <td></td><td></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

    {{-- Ações finais --}}
    <div class="bg-white rounded-2xl border border-coffee-100 shadow-sm p-6">
        <div class="flex flex-col sm:flex-row sm:items-center gap-3">
            @can('conclude', $secagem)
                <form method="POST" action="{{ route('secagens.conclude', $secagem) }}"
                      data-confirm="Concluir esta secagem?"
                      data-confirm-text="Os saldos dos clientes envolvidos serão debitados automaticamente e a secagem ficará bloqueada para edição."
                      data-confirm-icon="question"
                      data-confirm-yes="Sim, concluir"
                      data-confirm-danger="0"
                      class="w-full sm:w-auto">
                    @csrf
                    <button class="w-full sm:w-auto px-6 py-3 text-base font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition shadow-sm">
                        ✓ Concluir secagem
                    </button>
                </form>
                <p class="text-xs text-coffee-500 sm:max-w-xs">
                    Ao concluir, os saldos dos clientes envolvidos serão debitados automaticamente.
                </p>
            @endcan
            @can('delete', $secagem)
                <span class="hidden sm:flex flex-1"></span>
                <form method="POST" action="{{ route('secagens.destroy', $secagem) }}"
                      data-confirm="Excluir este rascunho?"
                      data-confirm-text="Os itens adicionados serão perdidos. Os saldos dos clientes não foram debitados ainda, então nada precisa ser revertido."
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
