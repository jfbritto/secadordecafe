@extends('layouts.app')

@section('title', 'Secagem #'.$secagem->numero)

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
    <div class="min-w-0">
        <p class="text-xs text-leaf-500 mb-1"><a href="{{ route('secagens.index') }}" class="hover:underline">Secagens</a></p>
        <h1 class="text-2xl font-bold text-leaf-900">Secagem #{{ $secagem->numero }}</h1>
        <p class="text-sm text-leaf-500 mt-0.5">
            {{ $secagem->data->format('d/m/Y') }} · {{ $secagem->secadorNome() }}
            @if($secagem->area)
                · <a href="{{ route('areas.show', $secagem->area) }}" class="text-leaf-700 font-semibold hover:underline">{{ $secagem->area->nome }}</a>
            @endif
            ·
            @if($secagem->isConcluida())
                <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 text-emerald-700">CONCLUÍDA em {{ $secagem->concluida_at->format('d/m/Y H:i') }}</span>
            @else
                <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-100 text-amber-700">RASCUNHO</span>
            @endif
        </p>
    </div>
    <div class="flex gap-2 flex-wrap">
        @can('reopen', $secagem)
            <form method="POST" action="{{ route('secagens.reopen', $secagem) }}"
                  data-confirm="Reabrir esta secagem pra corrigir?"
                  data-confirm-text="O sistema vai estornar os débitos dos clientes (devolver o café no saldo de cada um) e voltar a secagem pra rascunho. Você poderá ajustar e concluir de novo. O extrato vai registrar o estorno."
                  data-confirm-yes="Sim, reabrir">
                @csrf
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-amber-800 bg-amber-50 border border-amber-200 hover:bg-amber-100 rounded-lg transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"/></svg>
                    Reabrir pra corrigir
                </button>
            </form>
        @endcan
        <a href="{{ route('secagens.pdf', $secagem) }}" class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-leaf-700 bg-white border border-leaf-200 hover:bg-leaf-50 rounded-lg transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3"/></svg>
            PDF
        </a>
    </div>
</div>

@if($secagem->observacoes)
    <div class="bg-white rounded-xl border border-leaf-100 shadow-sm p-5 mb-4">
        <p class="text-xs font-semibold uppercase tracking-wider text-leaf-500 mb-1">Observações</p>
        <p class="text-leaf-700 whitespace-pre-wrap">{{ $secagem->observacoes }}</p>
    </div>
@endif

<div class="bg-white rounded-xl border border-leaf-100 shadow-sm overflow-hidden">
    {{-- Mobile: cards por cliente --}}
    <ul class="md:hidden divide-y divide-leaf-100">
        @foreach($secagem->items as $item)
            <li class="px-4 py-4">
                <p class="font-semibold text-leaf-900 mb-2">{{ $item->customer->nome }}</p>
                <div class="grid grid-cols-2 gap-x-3 gap-y-1.5 text-sm">
                    <div>
                        <p class="text-[10px] uppercase tracking-wider text-leaf-500">Recebido</p>
                        <p class="text-leaf-700">{{ number_format($item->quantidade_recebida_kg, 2, ',', '.') }} kg</p>
                    </div>
                    <div>
                        <p class="text-[10px] uppercase tracking-wider text-leaf-500">Seco</p>
                        <p class="text-leaf-700">{{ number_format($item->quantidade_seca_kg, 2, ',', '.') }} kg</p>
                    </div>
                    <div>
                        <p class="text-[10px] uppercase tracking-wider text-leaf-500">Rendimento</p>
                        <p class="text-leaf-700">{{ number_format($item->rendimentoPercentual(), 2, ',', '.') }}%</p>
                    </div>
                    <div>
                        <p class="text-[10px] uppercase tracking-wider text-leaf-500">Comissão</p>
                        <p class="text-leaf-700">{{ number_format($item->comissao_percentual, 2, ',', '.') }}% ({{ number_format($item->comissao_kg, 2, ',', '.') }} kg)</p>
                    </div>
                    <div class="col-span-2 pt-1 mt-1 border-t border-leaf-100">
                        <p class="text-[10px] uppercase tracking-wider text-leaf-500">Líquido</p>
                        <p class="font-bold text-leaf-800">{{ number_format($item->saldo_liquido_kg, 2, ',', '.') }} kg</p>
                    </div>
                </div>
            </li>
        @endforeach
        <li class="px-4 py-3 bg-leaf-50/50">
            <p class="text-[10px] uppercase tracking-wider text-leaf-500 mb-1 font-semibold">Totais</p>
            <div class="grid grid-cols-3 gap-2 text-sm">
                <div>
                    <p class="text-[10px] text-leaf-500">Recebido</p>
                    <p class="font-bold text-leaf-900">{{ number_format($secagem->totalRecebidoKg(), 2, ',', '.') }}</p>
                </div>
                <div>
                    <p class="text-[10px] text-leaf-500">Seco</p>
                    <p class="font-bold text-leaf-900">{{ number_format($secagem->totalSecoKg(), 2, ',', '.') }}</p>
                </div>
                <div>
                    <p class="text-[10px] text-leaf-500">Comissão</p>
                    <p class="font-bold text-leaf-900">{{ number_format($secagem->totalComissaoKg(), 2, ',', '.') }}</p>
                </div>
            </div>
        </li>
    </ul>

    {{-- Desktop: tabela --}}
    <table class="hidden md:table w-full text-sm">
        <thead class="bg-leaf-50/50 text-leaf-600 text-xs uppercase tracking-wider">
            <tr>
                <th class="text-left px-4 py-3 font-semibold">Cliente</th>
                <th class="text-right px-4 py-3 font-semibold">Recebido (kg)</th>
                <th class="text-right px-4 py-3 font-semibold">Seco (kg)</th>
                <th class="text-right px-4 py-3 font-semibold">Rendimento</th>
                <th class="text-right px-4 py-3 font-semibold">Comissão %</th>
                <th class="text-right px-4 py-3 font-semibold">Comissão (kg)</th>
                <th class="text-right px-4 py-3 font-semibold">Líquido (kg)</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-leaf-100">
            @foreach($secagem->items as $item)
                <tr class="hover:bg-leaf-50/30 transition">
                    <td class="px-4 py-3 text-leaf-900 font-medium">{{ $item->customer->nome }}</td>
                    <td class="px-4 py-3 text-right text-leaf-700">{{ number_format($item->quantidade_recebida_kg, 2, ',', '.') }}</td>
                    <td class="px-4 py-3 text-right text-leaf-700">{{ number_format($item->quantidade_seca_kg, 2, ',', '.') }}</td>
                    <td class="px-4 py-3 text-right text-leaf-700">{{ number_format($item->rendimentoPercentual(), 2, ',', '.') }}%</td>
                    <td class="px-4 py-3 text-right text-leaf-700">{{ number_format($item->comissao_percentual, 2, ',', '.') }}%</td>
                    <td class="px-4 py-3 text-right text-leaf-700">{{ number_format($item->comissao_kg, 2, ',', '.') }}</td>
                    <td class="px-4 py-3 text-right font-bold text-leaf-800">{{ number_format($item->saldo_liquido_kg, 2, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot class="bg-leaf-50/50 font-semibold text-leaf-900">
            <tr>
                <td class="px-4 py-3">Totais</td>
                <td class="px-4 py-3 text-right">{{ number_format($secagem->totalRecebidoKg(), 2, ',', '.') }}</td>
                <td class="px-4 py-3 text-right">{{ number_format($secagem->totalSecoKg(), 2, ',', '.') }}</td>
                <td></td><td></td>
                <td class="px-4 py-3 text-right">{{ number_format($secagem->totalComissaoKg(), 2, ',', '.') }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</div>
@endsection
