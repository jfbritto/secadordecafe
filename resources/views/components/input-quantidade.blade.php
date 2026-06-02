@props([
    'name',
    'value' => '',
    'required' => false,
    'id' => null,
    'step' => '0.01',
    'min' => '0.01',
    'max' => null,
])

@php
    $id = $id ?? $name;
    $valKg = $value !== '' && $value !== null ? (float) $value : '';
    $valSc = $valKg !== '' ? round($valKg / 60, 2) : '';
    $maxSc = $max !== null ? round((float) $max / 60, 2) : null;
@endphp

{{--
    Dois campos sincronizados: kg e sacos (1 sc = 60 kg). O produtor digita
    na unidade que preferir e o outro campo atualiza sozinho. Só o campo de kg
    carrega o `name` real — é ele que vai pro backend.
--}}
<div x-data="{
        kg: @js($valKg),
        sc: @js($valSc),
        fromKg() {
            this.sc = this.kg === '' || this.kg === null ? '' : (parseFloat(this.kg) / 60).toFixed(2);
            $dispatch('quantidade-change', { value: this.kg, name: @js($name) });
        },
        fromSc() {
            this.kg = this.sc === '' || this.sc === null ? '' : (parseFloat(this.sc) * 60).toFixed(2);
            $dispatch('quantidade-change', { value: this.kg, name: @js($name) });
        },
     }"
     class="grid grid-cols-2 gap-2">
    <div class="relative">
        <input id="{{ $id }}" type="number" step="{{ $step }}" min="{{ $min }}" @if($max !== null) max="{{ $max }}" @endif inputmode="decimal"
               name="{{ $name }}" @if($required) required @endif
               x-model="kg" @input="fromKg()" placeholder="0,00"
               {{ $attributes->merge(['class' => 'w-full pl-3 pr-9 py-3 text-base rounded-lg border border-leaf-200 placeholder-leaf-300 focus:border-leaf-500 focus:ring-4 focus:ring-leaf-500/15 outline-none transition']) }}>
        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-semibold text-leaf-500 pointer-events-none">kg</span>
    </div>
    <div class="relative">
        <input type="number" step="0.01" min="0" @if($maxSc !== null) max="{{ $maxSc }}" @endif inputmode="decimal"
               x-model="sc" @input="fromSc()" placeholder="0,00"
               class="w-full pl-3 pr-9 py-3 text-base rounded-lg border border-leaf-200 placeholder-leaf-300 focus:border-leaf-500 focus:ring-4 focus:ring-leaf-500/15 outline-none transition">
        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-semibold text-leaf-500 pointer-events-none">sc</span>
    </div>
</div>
