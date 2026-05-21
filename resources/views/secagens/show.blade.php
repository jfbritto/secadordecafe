@extends('layouts.app')

@section('title', 'Secagem #'.$secagem->numero)

@section('content')
<div class="flex items-center justify-between mb-6 gap-4 flex-wrap">
    <div>
        <p class="text-xs text-coffee-500 mb-1"><a href="{{ route('secagens.index') }}" class="hover:underline">Secagens</a></p>
        <h1 class="text-2xl font-bold text-coffee-900">Secagem #{{ $secagem->numero }}</h1>
        <p class="text-sm text-coffee-500 mt-0.5">
            {{ $secagem->data->format('d/m/Y') }} · {{ $secagem->secadorNome() }}
            @if($secagem->area)
                · <a href="{{ route('areas.show', $secagem->area) }}" class="text-coffee-700 font-semibold hover:underline">{{ $secagem->area->nome }}</a>
            @endif
            ·
            @if($secagem->isConcluida())
                <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 text-emerald-700">CONCLUÍDA em {{ $secagem->concluida_at->format('d/m/Y H:i') }}</span>
            @else
                <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-100 text-amber-700">RASCUNHO</span>
            @endif
        </p>
    </div>
    <a href="{{ route('secagens.pdf', $secagem) }}" class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-coffee-700 bg-white border border-coffee-200 hover:bg-coffee-50 rounded-lg transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3"/></svg>
        PDF
    </a>
</div>

@if($secagem->observacoes)
    <div class="bg-white rounded-xl border border-coffee-100 shadow-sm p-5 mb-4">
        <p class="text-xs font-semibold uppercase tracking-wider text-coffee-500 mb-1">Observações</p>
        <p class="text-coffee-700 whitespace-pre-wrap">{{ $secagem->observacoes }}</p>
    </div>
@endif

<div class="bg-white rounded-xl border border-coffee-100 shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-coffee-50/50 text-coffee-600 text-xs uppercase tracking-wider">
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
        <tbody class="divide-y divide-coffee-100">
            @foreach($secagem->items as $item)
                <tr class="hover:bg-coffee-50/30 transition">
                    <td class="px-4 py-3 text-coffee-900 font-medium">{{ $item->customer->nome }}</td>
                    <td class="px-4 py-3 text-right text-coffee-700">{{ number_format($item->quantidade_recebida_kg, 3, ',', '.') }}</td>
                    <td class="px-4 py-3 text-right text-coffee-700">{{ number_format($item->quantidade_seca_kg, 3, ',', '.') }}</td>
                    <td class="px-4 py-3 text-right text-coffee-700">{{ number_format($item->rendimentoPercentual(), 2, ',', '.') }}%</td>
                    <td class="px-4 py-3 text-right text-coffee-700">{{ number_format($item->comissao_percentual, 2, ',', '.') }}%</td>
                    <td class="px-4 py-3 text-right text-coffee-700">{{ number_format($item->comissao_kg, 3, ',', '.') }}</td>
                    <td class="px-4 py-3 text-right font-bold text-coffee-800">{{ number_format($item->saldo_liquido_kg, 3, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot class="bg-coffee-50/50 font-semibold text-coffee-900">
            <tr>
                <td class="px-4 py-3">Totais</td>
                <td class="px-4 py-3 text-right">{{ number_format($secagem->totalRecebidoKg(), 3, ',', '.') }}</td>
                <td class="px-4 py-3 text-right">{{ number_format($secagem->totalSecoKg(), 3, ',', '.') }}</td>
                <td></td><td></td>
                <td class="px-4 py-3 text-right">{{ number_format($secagem->totalComissaoKg(), 3, ',', '.') }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</div>
@endsection
