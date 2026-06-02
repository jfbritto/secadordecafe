@props([
    'titulo',
    'iconePath',
    'tone' => 'amber',
    'proprio' => 0,
    'terceiros' => 0,
    'urlProprio',
    'urlTerceiros',
    'urlTotal',
])

@php
    $proprio = (float) $proprio;
    $terceiros = (float) $terceiros;
    $total = $proprio + $terceiros;

    $tones = [
        'amber' => ['bg' => 'bg-amber-100', 'fg' => 'text-amber-700'],
        'emerald' => ['bg' => 'bg-emerald-100', 'fg' => 'text-emerald-700'],
    ];
    $tone = $tones[$tone] ?? $tones['amber'];

    $linha = function (string $rotulo, float $valor, string $url, bool $destaque = false) {
        $classes = $destaque
            ? 'flex items-center justify-between gap-3 px-5 py-3.5 hover:bg-leaf-50/60 transition group bg-leaf-50/40'
            : 'flex items-center justify-between gap-3 px-5 py-3.5 hover:bg-leaf-50/60 transition group';
        return [$classes, $rotulo, $valor, $url, $destaque];
    };

    $linhas = [
        $linha('Próprio', $proprio, $urlProprio),
        $linha('Terceiros', $terceiros, $urlTerceiros),
        $linha('Total', $total, $urlTotal, true),
    ];
@endphp

<div class="bg-white rounded-xl border border-leaf-100 shadow-sm overflow-hidden">
    <div class="px-5 pt-5 pb-3 flex items-center justify-between">
        <p class="text-xs font-semibold uppercase tracking-wider text-leaf-500">{{ $titulo }}</p>
        <div class="w-8 h-8 rounded-lg {{ $tone['bg'] }} {{ $tone['fg'] }} flex items-center justify-center">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconePath }}"/></svg>
        </div>
    </div>
    <div class="divide-y divide-leaf-100">
        @foreach($linhas as [$classes, $rotulo, $valor, $url, $destaque])
            <a href="{{ $url }}" class="{{ $classes }}">
                <span class="text-sm {{ $destaque ? 'font-bold text-leaf-900' : 'font-semibold text-leaf-700' }}">{{ $rotulo }}</span>
                <span class="flex items-baseline gap-2">
                    <span class="text-base {{ $destaque ? 'font-bold text-leaf-900' : 'font-bold text-leaf-800' }}">
                        {{ number_format($valor, 2, ',', '.') }} <span class="text-xs font-normal text-leaf-400">kg</span>
                    </span>
                    <span class="text-[10px] text-leaf-400 hidden sm:inline">{{ \App\Support\Sacos::formatSacos($valor) }}</span>
                    <span class="text-leaf-300 group-hover:text-leaf-600 transition">→</span>
                </span>
            </a>
        @endforeach
    </div>
</div>
