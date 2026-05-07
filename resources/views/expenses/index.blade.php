@extends('layouts.app')
@section('title', 'Despesas')
@section('content')

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:14px;">
    <h1 class="page" style="margin:0;">Despesas</h1>
    @can('create', App\Models\Expense::class)
        <a href="{{ route('despesas.create') }}" class="btn btn-primary" style="width:auto; padding:8px 14px;">+ Nova despesa</a>
    @endcan
</div>

@if(session('flash'))<div class="card" style="margin-bottom:12px; background:#dcfce7;">{{ session('flash') }}</div>@endif

<form method="GET" class="card" style="margin-bottom:12px;">
    <div style="display:grid; gap:8px; grid-template-columns:1fr 1fr 2fr auto;">
        <div class="field" style="margin:0;">
            <label>De</label>
            <input type="date" name="from" value="{{ $from }}">
        </div>
        <div class="field" style="margin:0;">
            <label>Até</label>
            <input type="date" name="to" value="{{ $to }}">
        </div>
        <div class="field" style="margin:0;">
            <label>Categoria</label>
            <select name="cat">
                <option value="">— todas —</option>
                @foreach(\App\Models\Expense::CATEGORIAS as $k => $v)
                    <option value="{{ $k }}" @selected($cat === $k)>{{ $v }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-primary" style="width:auto; padding:10px 14px; align-self:end;">Filtrar</button>
    </div>
</form>

<div class="card" style="margin-bottom:12px;">
    <strong>Totais por categoria{{ $from || $to ? ' (no período filtrado)' : '' }}</strong>
    <div style="display:grid; gap:8px; grid-template-columns:repeat(auto-fit,minmax(160px,1fr)); margin-top:10px;">
        @foreach(\App\Models\Expense::CATEGORIAS as $key => $label)
            @php $row = $totals[$key] ?? null; @endphp
            <div style="background:#f9f4ec; padding:10px; border-radius:8px;">
                <div style="font-size:12px; color:#7d6b58;">{{ $label }}</div>
                <div style="font-size:18px; font-weight:700; color:#5a3a22;">R$ {{ number_format($row->total ?? 0, 2, ',', '.') }}</div>
                <div style="font-size:11px; color:#7d6b58;">{{ $row->qtd ?? 0 }} lançamento(s)</div>
            </div>
        @endforeach
    </div>
    <hr style="margin:14px 0; border:0; border-top:1px solid #efe6d6;">
    <div style="display:flex; justify-content:space-between; align-items:baseline;">
        <strong>Total geral</strong>
        <span style="font-size:22px; font-weight:700; color:#5a3a22;">R$ {{ number_format($totalGeral, 2, ',', '.') }}</span>
    </div>
</div>

<div class="card" style="padding:0; overflow:hidden;">
    <table style="width:100%; border-collapse:collapse;">
        <thead style="background:#f9f4ec;">
            <tr>
                <th style="text-align:left; padding:10px 14px;">Data</th>
                <th style="text-align:left; padding:10px 14px;">Descrição</th>
                <th style="text-align:left; padding:10px 14px;">Categoria</th>
                <th style="text-align:right; padding:10px 14px;">Total (R$)</th>
                <th style="padding:10px 14px;"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($expenses as $e)
                <tr style="border-top:1px solid #efe6d6;">
                    <td style="padding:10px 14px;">{{ $e->data->format('d/m/Y') }}</td>
                    <td style="padding:10px 14px;">{{ $e->descricao }}</td>
                    <td style="padding:10px 14px;">{{ \App\Models\Expense::CATEGORIAS[$e->categoria] ?? $e->categoria }}</td>
                    <td style="padding:10px 14px; text-align:right;">{{ number_format($e->valor_total, 2, ',', '.') }}</td>
                    <td style="padding:10px 14px; text-align:right;">
                        @can('update', $e)<a href="{{ route('despesas.edit', $e) }}">editar</a>@endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" style="padding:24px; text-align:center; color:#7d6b58;">Nenhuma despesa.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div style="margin-top:14px;">{{ $expenses->links() }}</div>
@endsection
