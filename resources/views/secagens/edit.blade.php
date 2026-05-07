@extends('layouts.app')
@section('title', 'Secagem #'.$secagem->numero)
@section('content')

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:14px;">
    <div>
        <h1 class="page" style="margin:0;">Secagem #{{ $secagem->numero }}</h1>
        <small style="color:#7d6b58;">{{ $secagem->data->format('d/m/Y') }} · {{ $secagem->secador }} · <span class="badge badge-trial">RASCUNHO</span></small>
    </div>
    <a href="{{ route('secagens.index') }}">← voltar</a>
</div>

@if(session('flash'))<div class="card" style="margin-bottom:12px; background:#dcfce7;">{{ session('flash') }}</div>@endif
@if(session('error') || $errors->has('error'))
    <div class="card" style="margin-bottom:12px; background:#fee2e2; color:#991b1b;">{{ session('error') ?? $errors->first('error') }}</div>
@endif

<div style="display:grid; gap:16px; grid-template-columns:1fr 1fr;">
    <form method="POST" action="{{ route('secagens.update', $secagem) }}" class="card">
        @csrf @method('PUT')
        <strong>Dados gerais</strong>
        <div class="field" style="margin-top:10px;">
            <label>Data</label>
            <input type="date" name="data" value="{{ old('data', $secagem->data->format('Y-m-d')) }}" required>
        </div>
        <div class="field">
            <label>Secador</label>
            <input type="text" name="secador" value="{{ old('secador', $secagem->secador) }}" maxlength="80" required>
        </div>
        <div class="field">
            <label>Observações</label>
            <textarea name="observacoes" rows="2" style="width:100%; padding:10px 12px; border:1px solid #d6c9b6; border-radius:8px;">{{ old('observacoes', $secagem->observacoes) }}</textarea>
        </div>
        <button class="btn btn-primary" style="width:auto; padding:8px 14px;">Salvar</button>
    </form>

    <form method="POST" action="{{ route('secagens.items.store', $secagem) }}" class="card">
        @csrf
        <strong>Adicionar cliente</strong>
        <div class="field" style="margin-top:10px;">
            <label>Cliente</label>
            <select name="customer_id" required>
                <option value="">— selecionar —</option>
                @foreach($customers as $c)
                    <option value="{{ $c->id }}">{{ $c->nome }} (saldo {{ number_format($c->saldo_cafe_kg, 3, ',', '.') }} kg)</option>
                @endforeach
            </select>
            @error('customer_id')<small class="error">{{ $message }}</small>@enderror
        </div>
        <div style="display:grid; gap:8px; grid-template-columns:repeat(3, 1fr);">
            <div class="field">
                <label>Recebido (kg)</label>
                <input type="number" step="0.001" min="0.001" name="quantidade_recebida_kg" required>
            </div>
            <div class="field">
                <label>Seco (kg)</label>
                <input type="number" step="0.001" min="0.001" name="quantidade_seca_kg" required>
            </div>
            <div class="field">
                <label>Comissão (%)</label>
                <input type="number" step="0.01" min="0" max="100" name="comissao_percentual" value="0">
            </div>
        </div>
        @error('quantidade_recebida_kg')<small class="error">{{ $message }}</small>@enderror
        @error('quantidade_seca_kg')<small class="error">{{ $message }}</small>@enderror

        <button class="btn btn-primary" style="width:auto; padding:8px 14px;">Adicionar</button>
    </form>
</div>

<div class="card" style="margin-top:16px; padding:0; overflow:hidden;">
    <table style="width:100%; border-collapse:collapse;">
        <thead style="background:#f9f4ec;">
            <tr>
                <th style="text-align:left; padding:10px 14px;">Cliente</th>
                <th style="text-align:right; padding:10px 14px;">Recebido (kg)</th>
                <th style="text-align:right; padding:10px 14px;">Seco (kg)</th>
                <th style="text-align:right; padding:10px 14px;">Rendimento</th>
                <th style="text-align:right; padding:10px 14px;">Comissão (kg)</th>
                <th style="text-align:right; padding:10px 14px;">Líquido (kg)</th>
                <th style="padding:10px 14px;"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($secagem->items as $item)
                <tr style="border-top:1px solid #efe6d6;">
                    <td style="padding:10px 14px;">{{ $item->customer->nome }}</td>
                    <td style="padding:10px 14px; text-align:right;">{{ number_format($item->quantidade_recebida_kg, 3, ',', '.') }}</td>
                    <td style="padding:10px 14px; text-align:right;">{{ number_format($item->quantidade_seca_kg, 3, ',', '.') }}</td>
                    <td style="padding:10px 14px; text-align:right;">{{ number_format($item->rendimentoPercentual(), 2, ',', '.') }}%</td>
                    <td style="padding:10px 14px; text-align:right;">{{ number_format($item->comissao_kg, 3, ',', '.') }}</td>
                    <td style="padding:10px 14px; text-align:right;">{{ number_format($item->saldo_liquido_kg, 3, ',', '.') }}</td>
                    <td style="padding:10px 14px; text-align:right;">
                        <form method="POST" action="{{ route('secagens.items.destroy', [$secagem, $item]) }}" style="display:inline;" onsubmit="return confirm('Remover item?');">
                            @csrf @method('DELETE')
                            <button style="background:transparent; border:0; color:#a23b3b; cursor:pointer;">remover</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" style="padding:24px; text-align:center; color:#7d6b58;">Nenhum item adicionado.</td></tr>
            @endforelse
        </tbody>
        @if($secagem->items->isNotEmpty())
            <tfoot style="background:#f9f4ec; font-weight:600;">
                <tr>
                    <td style="padding:10px 14px;">Totais</td>
                    <td style="padding:10px 14px; text-align:right;">{{ number_format($secagem->totalRecebidoKg(), 3, ',', '.') }}</td>
                    <td style="padding:10px 14px; text-align:right;">{{ number_format($secagem->totalSecoKg(), 3, ',', '.') }}</td>
                    <td></td>
                    <td style="padding:10px 14px; text-align:right;">{{ number_format($secagem->totalComissaoKg(), 3, ',', '.') }}</td>
                    <td></td><td></td>
                </tr>
            </tfoot>
        @endif
    </table>
</div>

<div style="display:flex; gap:8px; margin-top:16px;">
    @can('conclude', $secagem)
        <form method="POST" action="{{ route('secagens.conclude', $secagem) }}" onsubmit="return confirm('Concluir secagem? Os saldos dos clientes serão debitados.');">
            @csrf
            <button class="btn btn-primary" style="width:auto; padding:9px 18px;">Concluir secagem</button>
        </form>
    @endcan
    @can('delete', $secagem)
        <form method="POST" action="{{ route('secagens.destroy', $secagem) }}" onsubmit="return confirm('Excluir rascunho?');">
            @csrf @method('DELETE')
            <button style="background:transparent; border:0; color:#a23b3b; cursor:pointer;">Excluir rascunho</button>
        </form>
    @endcan
</div>
@endsection
