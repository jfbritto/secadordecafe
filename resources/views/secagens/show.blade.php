@extends('layouts.app')
@section('title', 'Secagem #'.$secagem->numero)
@section('content')

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:14px;">
    <div>
        <h1 class="page" style="margin:0;">Secagem #{{ $secagem->numero }}</h1>
        <small style="color:#7d6b58;">
            {{ $secagem->data->format('d/m/Y') }} · {{ $secagem->secador }} ·
            @if($secagem->isConcluida())
                <span class="badge badge-active">CONCLUÍDA em {{ $secagem->concluida_at->format('d/m/Y H:i') }}</span>
            @else
                <span class="badge badge-trial">RASCUNHO</span>
            @endif
        </small>
    </div>
    <div>
        <a href="{{ route('secagens.pdf', $secagem) }}" class="btn btn-primary" style="width:auto; padding:8px 14px; margin-right:6px;">⬇ PDF</a>
        <a href="{{ route('secagens.index') }}">← voltar</a>
    </div>
</div>

@if($secagem->observacoes)
    <div class="card" style="margin-bottom:14px;"><strong>Observações:</strong> {{ $secagem->observacoes }}</div>
@endif

<div class="card" style="padding:0; overflow:hidden;">
    <table style="width:100%; border-collapse:collapse;">
        <thead style="background:#f9f4ec;">
            <tr>
                <th style="text-align:left; padding:10px 14px;">Cliente</th>
                <th style="text-align:right; padding:10px 14px;">Recebido (kg)</th>
                <th style="text-align:right; padding:10px 14px;">Seco (kg)</th>
                <th style="text-align:right; padding:10px 14px;">Rendimento</th>
                <th style="text-align:right; padding:10px 14px;">Comissão %</th>
                <th style="text-align:right; padding:10px 14px;">Comissão (kg)</th>
                <th style="text-align:right; padding:10px 14px;">Líquido (kg)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($secagem->items as $item)
                <tr style="border-top:1px solid #efe6d6;">
                    <td style="padding:10px 14px;">{{ $item->customer->nome }}</td>
                    <td style="padding:10px 14px; text-align:right;">{{ number_format($item->quantidade_recebida_kg, 3, ',', '.') }}</td>
                    <td style="padding:10px 14px; text-align:right;">{{ number_format($item->quantidade_seca_kg, 3, ',', '.') }}</td>
                    <td style="padding:10px 14px; text-align:right;">{{ number_format($item->rendimentoPercentual(), 2, ',', '.') }}%</td>
                    <td style="padding:10px 14px; text-align:right;">{{ number_format($item->comissao_percentual, 2, ',', '.') }}%</td>
                    <td style="padding:10px 14px; text-align:right;">{{ number_format($item->comissao_kg, 3, ',', '.') }}</td>
                    <td style="padding:10px 14px; text-align:right;">{{ number_format($item->saldo_liquido_kg, 3, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot style="background:#f9f4ec; font-weight:600;">
            <tr>
                <td style="padding:10px 14px;">Totais</td>
                <td style="padding:10px 14px; text-align:right;">{{ number_format($secagem->totalRecebidoKg(), 3, ',', '.') }}</td>
                <td style="padding:10px 14px; text-align:right;">{{ number_format($secagem->totalSecoKg(), 3, ',', '.') }}</td>
                <td></td><td></td>
                <td style="padding:10px 14px; text-align:right;">{{ number_format($secagem->totalComissaoKg(), 3, ',', '.') }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</div>
@endsection
