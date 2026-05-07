@extends('layouts.app')

@section('title', 'Secagem #'.$secagem->numero)

@section('content')
<div class="flex items-center justify-between mb-6 gap-4 flex-wrap">
    <div>
        <p class="text-xs text-coffee-500 mb-1"><a href="{{ route('secagens.index') }}" class="hover:underline">Secagens</a></p>
        <h1 class="text-2xl font-bold text-coffee-900">Secagem #{{ $secagem->numero }}</h1>
        <p class="text-sm text-coffee-500 mt-0.5">
            {{ $secagem->data->format('d/m/Y') }} · {{ $secagem->secador }} ·
            <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-100 text-amber-700">RASCUNHO</span>
        </p>
    </div>
</div>

<div class="grid lg:grid-cols-2 gap-4 mb-6">
    <form method="POST" action="{{ route('secagens.update', $secagem) }}" class="bg-white rounded-xl border border-coffee-100 shadow-sm p-6 space-y-4">
        @csrf @method('PUT')
        <h2 class="text-sm font-bold text-coffee-900 uppercase tracking-wider">Dados gerais</h2>

        <div>
            <label class="block text-xs font-semibold text-coffee-700 mb-1.5">Data</label>
            <input type="date" name="data" value="{{ old('data', $secagem->data->format('Y-m-d')) }}" required
                   class="w-full px-3 py-2 text-sm rounded-lg border border-coffee-200 focus:outline-none focus:ring-2 focus:ring-coffee-500">
        </div>
        <div>
            <label class="block text-xs font-semibold text-coffee-700 mb-1.5">Secador</label>
            <input type="text" name="secador" value="{{ old('secador', $secagem->secador) }}" maxlength="80" required
                   class="w-full px-3 py-2 text-sm rounded-lg border border-coffee-200 focus:outline-none focus:ring-2 focus:ring-coffee-500">
        </div>
        <div>
            <label class="block text-xs font-semibold text-coffee-700 mb-1.5">Observações</label>
            <textarea name="observacoes" rows="2"
                      class="w-full px-3 py-2 text-sm rounded-lg border border-coffee-200 focus:outline-none focus:ring-2 focus:ring-coffee-500">{{ old('observacoes', $secagem->observacoes) }}</textarea>
        </div>
        <button class="px-4 py-2 text-sm font-semibold text-white bg-coffee-700 hover:bg-coffee-800 rounded-lg transition">Salvar</button>
    </form>

    <form method="POST" action="{{ route('secagens.items.store', $secagem) }}" class="bg-white rounded-xl border border-coffee-100 shadow-sm p-6 space-y-3">
        @csrf
        <h2 class="text-sm font-bold text-coffee-900 uppercase tracking-wider">Adicionar cliente</h2>

        <div>
            <label class="block text-xs font-semibold text-coffee-700 mb-1.5">Cliente</label>
            <select name="customer_id" required class="w-full px-3 py-2 text-sm rounded-lg border border-coffee-200 focus:outline-none focus:ring-2 focus:ring-coffee-500">
                <option value="">— selecionar —</option>
                @foreach($customers as $c)
                    <option value="{{ $c->id }}">{{ $c->nome }} (saldo {{ number_format($c->saldo_cafe_kg, 3, ',', '.') }} kg)</option>
                @endforeach
            </select>
            @error('customer_id')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
        </div>

        <div class="grid grid-cols-3 gap-2">
            <div>
                <label class="block text-xs font-semibold text-coffee-700 mb-1.5">Recebido (kg)</label>
                <input type="number" step="0.001" min="0.001" name="quantidade_recebida_kg" required
                       class="w-full px-3 py-2 text-sm rounded-lg border border-coffee-200 focus:outline-none focus:ring-2 focus:ring-coffee-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-coffee-700 mb-1.5">Seco (kg)</label>
                <input type="number" step="0.001" min="0.001" name="quantidade_seca_kg" required
                       class="w-full px-3 py-2 text-sm rounded-lg border border-coffee-200 focus:outline-none focus:ring-2 focus:ring-coffee-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-coffee-700 mb-1.5">Comissão (%)</label>
                <input type="number" step="0.01" min="0" max="100" name="comissao_percentual" value="0"
                       class="w-full px-3 py-2 text-sm rounded-lg border border-coffee-200 focus:outline-none focus:ring-2 focus:ring-coffee-500">
            </div>
        </div>
        @error('quantidade_recebida_kg')<p class="text-xs text-rose-600">{{ $message }}</p>@enderror
        @error('quantidade_seca_kg')<p class="text-xs text-rose-600">{{ $message }}</p>@enderror

        <button class="px-4 py-2 text-sm font-semibold text-white bg-coffee-600 hover:bg-coffee-700 rounded-lg transition">Adicionar item</button>
    </form>
</div>

<div class="bg-white rounded-xl border border-coffee-100 shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-coffee-50/50 text-coffee-600 text-xs uppercase tracking-wider">
            <tr>
                <th class="text-left px-4 py-3 font-semibold">Cliente</th>
                <th class="text-right px-4 py-3 font-semibold">Recebido</th>
                <th class="text-right px-4 py-3 font-semibold">Seco</th>
                <th class="text-right px-4 py-3 font-semibold">Rendimento</th>
                <th class="text-right px-4 py-3 font-semibold">Comissão (kg)</th>
                <th class="text-right px-4 py-3 font-semibold">Líquido (kg)</th>
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
                        <form method="POST" action="{{ route('secagens.items.destroy', [$secagem, $item]) }}" onsubmit="return confirm('Remover item?');" class="inline">
                            @csrf @method('DELETE')
                            <button class="text-rose-600 text-xs hover:underline">remover</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-6 py-12 text-center text-coffee-500">Nenhum item adicionado.</td></tr>
            @endforelse
        </tbody>
        @if($secagem->items->isNotEmpty())
            <tfoot class="bg-coffee-50/50 font-semibold text-coffee-900">
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

<div class="flex items-center gap-3 mt-6">
    @can('conclude', $secagem)
        <form method="POST" action="{{ route('secagens.conclude', $secagem) }}" onsubmit="return confirm('Concluir secagem? Os saldos dos clientes serão debitados.');">
            @csrf
            <button class="px-5 py-2.5 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition shadow-sm">Concluir secagem</button>
        </form>
    @endcan
    @can('delete', $secagem)
        <form method="POST" action="{{ route('secagens.destroy', $secagem) }}" onsubmit="return confirm('Excluir rascunho?');">
            @csrf @method('DELETE')
            <button class="px-3 py-2 text-sm text-rose-600 hover:bg-rose-50 rounded-lg transition">Excluir rascunho</button>
        </form>
    @endcan
</div>
@endsection
