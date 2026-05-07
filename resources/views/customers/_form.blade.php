@csrf
<div class="field">
    <label>Nome *</label>
    <input type="text" name="nome" value="{{ old('nome', $customer->nome ?? '') }}" required maxlength="150">
    @error('nome')<small class="error">{{ $message }}</small>@enderror
</div>

<div class="field">
    <label>Telefone</label>
    <input type="text" name="telefone" value="{{ old('telefone', $customer->telefone ?? '') }}" maxlength="30">
    @error('telefone')<small class="error">{{ $message }}</small>@enderror
</div>

<div class="field">
    <label>CPF / CNPJ</label>
    <input type="text" name="cpf_cnpj" value="{{ old('cpf_cnpj', $customer->cpf_cnpj ?? '') }}" maxlength="20">
    @error('cpf_cnpj')<small class="error">{{ $message }}</small>@enderror
</div>

<div class="field">
    <label>Saldo inicial de café (kg)</label>
    <input type="number" step="0.001" min="0" name="saldo_cafe_kg" value="{{ old('saldo_cafe_kg', $customer->saldo_cafe_kg ?? 0) }}">
    <small style="color:#7d6b58;">A partir da Fase 3, este valor passa a ser controlado por movimentações.</small>
    @error('saldo_cafe_kg')<small class="error">{{ $message }}</small>@enderror
</div>

<div class="field">
    <label>Observações</label>
    <textarea name="observacoes" rows="3" style="width:100%; padding:10px 12px; border:1px solid #d6c9b6; border-radius:8px;">{{ old('observacoes', $customer->observacoes ?? '') }}</textarea>
    @error('observacoes')<small class="error">{{ $message }}</small>@enderror
</div>
