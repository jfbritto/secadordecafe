<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Secagem #{{ $secagem->numero }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color:#222; font-size:12px; }
        h1 { color:#1e5631; margin:0 0 4px; font-size:18px; }
        .muted { color:#666; font-size:11px; }
        .header { display:flex; justify-content:space-between; align-items:flex-end; border-bottom:2px solid #1e5631; padding-bottom:8px; margin-bottom:12px; }
        table { width:100%; border-collapse:collapse; margin-top:10px; }
        th, td { padding:6px 8px; border-bottom:1px solid #d8c9a8; font-size:11px; }
        th { background:#f4ead4; text-align:left; }
        tfoot td { background:#f4ead4; font-weight:bold; }
        .right { text-align:right; }
        .badge { padding:2px 8px; border-radius:10px; font-size:10px; font-weight:bold; }
        .badge-active { background:#dcfce7; color:#166534; }
        .badge-trial { background:#fef3c7; color:#92400e; }
    </style>
</head>
<body>

<div class="header">
    <div>
        <h1>Secagem #{{ $secagem->numero }}</h1>
        <div class="muted">{{ $secagem->farm->nome }} · {{ $secagem->data->format('d/m/Y') }} · Secador: {{ $secagem->secadorNome() }}</div>
    </div>
    <div>
        @if($secagem->isConcluida())
            <span class="badge badge-active">CONCLUÍDA em {{ $secagem->concluida_at->format('d/m/Y H:i') }}</span>
        @else
            <span class="badge badge-trial">RASCUNHO</span>
        @endif
    </div>
</div>

@if($secagem->observacoes)
    <p><strong>Observações:</strong> {{ $secagem->observacoes }}</p>
@endif

<table>
    <thead>
        <tr>
            <th>Origem</th>
            <th class="right">Recebido (sc / kg)</th>
            <th class="right">Seco (sc / kg)</th>
            <th class="right">Rendimento</th>
            <th class="right">Proporção</th>
            <th class="right">Comissão %</th>
            <th class="right">Comissão (kg)</th>
            <th class="right">Líquido (kg)</th>
        </tr>
    </thead>
    <tbody>
        @foreach($secagem->items as $item)
            <tr>
                <td>{{ ($item->isArea() ? '[Area] ' : '') . $item->originLabel() }}</td>
                <td class="right">{{ \App\Support\Sacos::formatSacos($item->quantidade_recebida_kg) }} / {{ number_format($item->quantidade_recebida_kg, 2, ',', '.') }}</td>
                <td class="right">{{ \App\Support\Sacos::formatSacos($item->quantidade_seca_kg) }} / {{ number_format($item->quantidade_seca_kg, 2, ',', '.') }}</td>
                <td class="right">{{ number_format($item->rendimentoPercentual(), 2, ',', '.') }}%</td>
                <td class="right">@if($item->proporcaoCocoSeco() > 0){{ number_format($item->proporcaoCocoSeco(), 2, ',', '.') }} : 1@else—@endif</td>
                <td class="right">{{ number_format($item->comissao_percentual, 2, ',', '.') }}%</td>
                <td class="right">{{ number_format($item->comissao_kg, 2, ',', '.') }}</td>
                <td class="right">{{ number_format($item->saldo_liquido_kg, 2, ',', '.') }}</td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td>Totais</td>
            <td class="right">{{ \App\Support\Sacos::formatSacos($secagem->totalRecebidoKg()) }} / {{ number_format($secagem->totalRecebidoKg(), 2, ',', '.') }}</td>
            <td class="right">{{ \App\Support\Sacos::formatSacos($secagem->totalSecoKg()) }} / {{ number_format($secagem->totalSecoKg(), 2, ',', '.') }}</td>
            <td></td><td></td><td></td>
            <td class="right">{{ number_format($secagem->totalComissaoKg(), 2, ',', '.') }}</td>
            <td></td>
        </tr>
    </tfoot>
</table>

<p class="muted" style="margin-top:24px;">Documento gerado em {{ now()->format('d/m/Y H:i') }} · Roça Nossa</p>

</body>
</html>
