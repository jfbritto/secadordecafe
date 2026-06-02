@extends('layouts.app')

@section('title', 'Compras de café')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-3">
    <div>
        <h1 class="text-2xl font-bold text-leaf-900">Compras de café</h1>
        <p class="text-sm text-leaf-500 mt-0.5">Café côco ou seco comprado de terceiros pra revender. Entra no estoque da fazenda.</p>
    </div>
    @can('create', App\Models\Expense::class)
        <a href="{{ route('compras.create') }}" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-semibold text-white bg-leaf-700 hover:bg-leaf-800 rounded-lg transition shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Nova compra
        </a>
    @endcan
</div>

@if($compras->isEmpty())
    <div class="bg-white rounded-xl border border-leaf-100 shadow-sm p-8 text-center">
        <p class="text-leaf-500">Nenhuma compra de café registrada ainda.</p>
        @can('create', App\Models\Expense::class)
            <a href="{{ route('compras.create') }}" class="inline-block mt-3 text-leaf-700 font-semibold hover:underline">Registrar a primeira →</a>
        @endcan
    </div>
@else
    {{-- Mobile: cards --}}
    <div class="md:hidden space-y-3">
        @foreach($compras as $c)
            @php
                $mov = $c->movement ?? null;
                $produto = $mov?->produto;
                $produtoClasse = $produto === 'coco' ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700';
                $produtoLabel = $produto ? \App\Support\StatusLabels::produto($produto) : '—';
            @endphp
            <div class="bg-white rounded-xl border border-leaf-100 shadow-sm p-4">
                <div class="flex items-start justify-between gap-3 mb-2">
                    <div class="min-w-0">
                        <p class="text-xs text-leaf-500">{{ $c->data->format('d/m/Y') }}</p>
                        <p class="text-sm font-semibold text-leaf-900 truncate">{{ $c->descricao }}</p>
                    </div>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $produtoClasse }}">{{ $produtoLabel }}</span>
                </div>
                <div class="grid grid-cols-2 gap-2 text-sm">
                    <div>
                        <p class="text-[10px] uppercase text-leaf-500">Quantidade</p>
                        <p class="font-semibold text-leaf-800">{{ number_format((float) $c->quantidade, 2, ',', '.') }} kg</p>
                        <p class="text-[10px] text-leaf-400">{{ \App\Support\Sacos::formatSacos((float) $c->quantidade) }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] uppercase text-leaf-500">Total</p>
                        <p class="font-bold text-rose-600">R$ {{ number_format((float) $c->valor_total, 2, ',', '.') }}</p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Desktop: tabela --}}
    <div class="hidden md:block bg-white rounded-xl border border-leaf-100 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-leaf-50/50 text-leaf-600 text-xs uppercase tracking-wider">
                <tr>
                    <th class="text-left px-4 py-3 font-semibold">Data</th>
                    <th class="text-left px-4 py-3 font-semibold">Produto</th>
                    <th class="text-left px-4 py-3 font-semibold">Descrição</th>
                    <th class="text-right px-4 py-3 font-semibold">Quantidade</th>
                    <th class="text-right px-4 py-3 font-semibold">Valor unit.</th>
                    <th class="text-right px-4 py-3 font-semibold">Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-leaf-100">
                @foreach($compras as $c)
                    @php
                        $mov = $c->movement ?? null;
                        $produto = $mov?->produto;
                        $produtoClasse = $produto === 'coco' ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700';
                        $produtoLabel = $produto ? \App\Support\StatusLabels::produto($produto) : '—';
                    @endphp
                    <tr class="hover:bg-leaf-50/30 transition">
                        <td class="px-4 py-3 text-leaf-700 whitespace-nowrap">{{ $c->data->format('d/m/Y') }}</td>
                        <td class="px-4 py-3"><span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $produtoClasse }}">{{ $produtoLabel }}</span></td>
                        <td class="px-4 py-3 text-leaf-900">{{ $c->descricao }}</td>
                        <td class="px-4 py-3 text-right text-leaf-700">
                            {{ number_format((float) $c->quantidade, 2, ',', '.') }} kg
                            <span class="block text-[10px] text-leaf-400">{{ \App\Support\Sacos::formatSacos((float) $c->quantidade) }}</span>
                        </td>
                        <td class="px-4 py-3 text-right text-leaf-700">R$ {{ number_format((float) $c->valor_unitario, 2, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right font-bold text-rose-600">R$ {{ number_format((float) $c->valor_total, 2, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $compras->links() }}
    </div>
@endif
@endsection
