@csrf
<div style="display:grid; gap:8px; grid-template-columns:1fr 1fr;">
    <div class="field">
        <label>Data *</label>
        <input type="date" name="data" value="{{ old('data', isset($expense) ? $expense->data->format('Y-m-d') : now()->format('Y-m-d')) }}" required>
        @error('data')<small class="error">{{ $message }}</small>@enderror
    </div>
    <div class="field">
        <label>Categoria *</label>
        <select name="categoria" required>
            @foreach(\App\Models\Expense::CATEGORIAS as $key => $label)
                <option value="{{ $key }}" @selected(old('categoria', $expense->categoria ?? '') === $key)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="field">
    <label>Descrição *</label>
    <input type="text" name="descricao" maxlength="200" required value="{{ old('descricao', $expense->descricao ?? '') }}">
    @error('descricao')<small class="error">{{ $message }}</small>@enderror
</div>

<div style="display:grid; gap:8px; grid-template-columns:1fr 1fr 1fr 1.4fr;">
    <div class="field">
        <label>Unidade</label>
        <input type="text" name="unidade" maxlength="20" value="{{ old('unidade', $expense->unidade ?? '') }}">
    </div>
    <div class="field">
        <label>Quantidade</label>
        <input type="number" step="0.001" min="0.001" name="quantidade" value="{{ old('quantidade', $expense->quantidade ?? 1) }}">
    </div>
    <div class="field">
        <label>Valor unitário (R$)</label>
        <input type="number" step="0.01" min="0" name="valor_unitario" value="{{ old('valor_unitario', $expense->valor_unitario ?? 0) }}">
    </div>
    <div class="field">
        <label>Valor total (R$) *</label>
        <input type="number" step="0.01" min="0.01" name="valor_total" required value="{{ old('valor_total', $expense->valor_total ?? '') }}">
        @error('valor_total')<small class="error">{{ $message }}</small>@enderror
    </div>
</div>

<div class="field">
    <label>Observações</label>
    <textarea name="observacoes" rows="2" style="width:100%; padding:10px 12px; border:1px solid #d6c9b6; border-radius:8px;">{{ old('observacoes', $expense->observacoes ?? '') }}</textarea>
</div>
