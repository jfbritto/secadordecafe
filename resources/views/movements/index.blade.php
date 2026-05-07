@extends('layouts.app')

@section('title', 'Extrato — '.$customer->nome)

@section('content')
<div style="display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:14px;">
    <div>
        <h1 class="page" style="margin:0;">{{ $customer->nome }}</h1>
        <small style="color:#7d6b58;">Saldo atual: <strong>{{ number_format($customer->saldo_cafe_kg, 3, ',', '.') }} kg</strong></small>
    </div>
    <a href="{{ route('clientes.show', $customer) }}">← cliente</a>
</div>

@if(session('flash'))<div class="card" style="margin-bottom:12px; background:#dcfce7;">{{ session('flash') }}</div>@endif

<div class="card" style="margin-bottom:16px;">
    <strong>Nova movimentação</strong>
    <form method="POST" action="{{ route('clientes.movimentacoes.store', $customer) }}" style="margin-top:10px;">
        @csrf
        <div style="display:flex; gap:8px; flex-wrap:wrap; align-items:end;">
            <div class="field" style="margin:0;">
                <label>Tipo</label>
                <select name="tipo" id="tipoSelect">
                    <option value="entrada">Entrada</option>
                    @if(auth()->user()->hasAnyRole(['admin','operador']))<option value="ajuste">Ajuste</option>@endif
                    @if(auth()->user()->hasRole('admin'))<option value="saida">Saída</option>@endif
                </select>
            </div>
            <div class="field" style="margin:0;" id="direcaoWrap" hidden>
                <label>Direção</label>
                <select name="direcao">
                    <option value="+">+</option>
                    <option value="-">−</option>
                </select>
            </div>
            <div class="field" style="margin:0;">
                <label>Qtd. (kg)</label>
                <input type="number" step="0.001" min="0.001" name="quantidade" required style="width:120px;">
            </div>
            <div class="field" style="margin:0; flex:1; min-width:200px;">
                <label>Observação</label>
                <input type="text" name="observacao" maxlength="500">
            </div>
            <button type="submit" class="btn btn-primary" style="width:auto; padding:10px 16px;">Registrar</button>
        </div>
        @error('tipo')<small class="error">{{ $message }}</small>@enderror
        @error('quantidade')<small class="error">{{ $message }}</small>@enderror
    </form>
</div>

<div class="card" style="padding:0; overflow:hidden;">
    <table style="width:100%; border-collapse:collapse;">
        <thead style="background:#f9f4ec;">
            <tr>
                <th style="text-align:left; padding:10px 14px;">Quando</th>
                <th style="text-align:left; padding:10px 14px;">Tipo</th>
                <th style="text-align:right; padding:10px 14px;">Quantidade (kg)</th>
                <th style="text-align:left; padding:10px 14px;">Observação</th>
                <th style="text-align:left; padding:10px 14px;">Por</th>
            </tr>
        </thead>
        <tbody>
            @forelse($movements as $m)
                <tr style="border-top:1px solid #efe6d6;">
                    <td style="padding:10px 14px;">{{ $m->occurred_at->format('d/m/Y H:i') }}</td>
                    <td style="padding:10px 14px;">{{ ucfirst($m->tipo) }}</td>
                    <td style="padding:10px 14px; text-align:right; color:{{ $m->quantidade_kg < 0 ? '#a23b3b' : '#166534' }};">
                        {{ ($m->quantidade_kg > 0 ? '+' : '') }}{{ number_format($m->quantidade_kg, 3, ',', '.') }}
                    </td>
                    <td style="padding:10px 14px;">{{ $m->observacao ?? '—' }}</td>
                    <td style="padding:10px 14px;">{{ $m->user?->name ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="5" style="padding:24px; text-align:center; color:#7d6b58;">Sem movimentações.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div style="margin-top:14px;">{{ $movements->links() }}</div>

<script>
document.getElementById('tipoSelect')?.addEventListener('change', function(){
    document.getElementById('direcaoWrap').hidden = this.value !== 'ajuste';
});
</script>
@endsection
