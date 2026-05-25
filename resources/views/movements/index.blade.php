@extends('layouts.app')

@section('title', 'Extrato, '.$customer->nome)

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between mb-6 gap-4">
        <div class="min-w-0">
            <p class="text-xs text-leaf-500 mb-1">
                <a href="{{ route('clientes.index') }}" class="hover:underline">Clientes</a> ·
                <a href="{{ route('clientes.show', $customer) }}" class="hover:underline">{{ $customer->nome }}</a> ·
                <span class="text-leaf-700">Extrato</span>
            </p>
            <h1 class="text-2xl font-bold text-leaf-900 break-words">Extrato, {{ $customer->nome }}</h1>
        </div>
        <div class="bg-leaf-700 text-white px-5 py-3 rounded-xl shadow-md text-right flex-shrink-0">
            <p class="text-[10px] uppercase tracking-wider text-leaf-200">Saldo atual</p>
            <p class="text-2xl font-bold">{{ number_format($customer->saldo_cafe_kg, 3, ',', '.') }} <span class="text-sm font-normal text-leaf-200">kg</span></p>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-leaf-100 shadow-sm p-6 mb-6" x-data="{ tipo: 'entrada' }">
        <div class="border-b border-leaf-100 pb-4 mb-5">
            <h2 class="text-base font-bold text-leaf-900">Nova movimentação</h2>
            <p class="text-sm text-leaf-500 mt-0.5">Use para registrar entradas (cliente trouxe café), ajustes ou saídas avulsas.</p>
        </div>

        <form method="POST" action="{{ route('clientes.movimentacoes.store', $customer) }}">
            @csrf

            <div class="mb-5">
                <label class="block text-sm font-bold text-leaf-900 mb-3">
                    Tipo <span class="text-rose-500">*</span>
                </label>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <label class="flex items-start gap-3 p-4 rounded-lg border-2 border-leaf-200 cursor-pointer hover:bg-leaf-50/50 transition has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50">
                        <input type="radio" name="tipo" value="entrada" x-model="tipo" checked
                               class="mt-0.5 w-5 h-5 border-leaf-300 text-emerald-600 focus:ring-emerald-500">
                        <div>
                            <span class="block text-sm font-bold text-leaf-900">+ Entrada</span>
                            <span class="block text-xs text-leaf-500 mt-0.5">Cliente trouxe café para a fazenda. Soma ao saldo.</span>
                        </div>
                    </label>

                    @if(auth()->user()->hasAnyRole(['admin','operador']))
                    <label class="flex items-start gap-3 p-4 rounded-lg border-2 border-leaf-200 cursor-pointer hover:bg-leaf-50/50 transition has-[:checked]:border-amber-500 has-[:checked]:bg-amber-50">
                        <input type="radio" name="tipo" value="ajuste" x-model="tipo"
                               class="mt-0.5 w-5 h-5 border-leaf-300 text-amber-600 focus:ring-amber-500">
                        <div>
                            <span class="block text-sm font-bold text-leaf-900">± Ajuste</span>
                            <span class="block text-xs text-leaf-500 mt-0.5">Correção de saldo. Pode ser para mais (+) ou para menos (−).</span>
                        </div>
                    </label>
                    @endif

                    @if(auth()->user()->hasRole('admin'))
                    <label class="flex items-start gap-3 p-4 rounded-lg border-2 border-leaf-200 cursor-pointer hover:bg-leaf-50/50 transition has-[:checked]:border-rose-500 has-[:checked]:bg-rose-50">
                        <input type="radio" name="tipo" value="saida" x-model="tipo"
                               class="mt-0.5 w-5 h-5 border-leaf-300 text-rose-600 focus:ring-rose-500">
                        <div>
                            <span class="block text-sm font-bold text-leaf-900">− Saída</span>
                            <span class="block text-xs text-leaf-500 mt-0.5">Café retirado pelo cliente sem secagem. Subtrai do saldo.</span>
                        </div>
                    </label>
                    @endif
                </div>
                @error('tipo')<p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div class="grid sm:grid-cols-12 gap-4">
                <div class="sm:col-span-3" x-show="tipo === 'ajuste'" x-cloak>
                    <label for="direcao" class="block text-sm font-bold text-leaf-900 mb-2">Direção</label>
                    <select id="direcao" name="direcao"
                            class="w-full px-4 py-3 text-base rounded-lg border border-leaf-200 focus:border-leaf-500 focus:ring-4 focus:ring-leaf-500/15 outline-none transition bg-white">
                        <option value="+">+ Crédito (somar)</option>
                        <option value="-">− Débito (subtrair)</option>
                    </select>
                </div>

                <div :class="tipo === 'ajuste' ? 'sm:col-span-3' : 'sm:col-span-4'">
                    <label for="quantidade" class="block text-sm font-bold text-leaf-900 mb-2">
                        Quantidade <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <input id="quantidade" type="number" step="0.001" min="0.001" inputmode="decimal" name="quantidade" required
                               placeholder="0,000"
                               class="w-full pl-4 pr-12 py-3 text-base rounded-lg border border-leaf-200 placeholder-leaf-300 focus:border-leaf-500 focus:ring-4 focus:ring-leaf-500/15 outline-none transition">
                        <span class="absolute right-4 top-1/2 -translate-y-1/2 text-sm font-semibold text-leaf-500 pointer-events-none">kg</span>
                    </div>
                    @error('quantidade')<p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div :class="tipo === 'ajuste' ? 'sm:col-span-4' : 'sm:col-span-6'">
                    <label for="observacao" class="block text-sm font-bold text-leaf-900 mb-2">Observação</label>
                    <input id="observacao" type="text" name="observacao" maxlength="500"
                           placeholder="Ex: Lote do dia 5, ajuste de balança…"
                           class="w-full px-4 py-3 text-base rounded-lg border border-leaf-200 placeholder-leaf-300 focus:border-leaf-500 focus:ring-4 focus:ring-leaf-500/15 outline-none transition">
                </div>

                <div class="sm:col-span-2 flex items-end col-span-full sm:col-auto">
                    <button type="submit" class="w-full px-4 py-3 text-base font-bold text-white bg-leaf-700 hover:bg-leaf-800 rounded-lg transition shadow-sm">Registrar</button>
                </div>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-2xl border border-leaf-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-leaf-100">
            <h2 class="text-sm font-bold text-leaf-900 uppercase tracking-wider">Histórico</h2>
        </div>

        {{-- Mobile: cards (extrato bancário em formato vertical) --}}
        <ul class="md:hidden divide-y divide-leaf-100">
            @forelse($movements as $m)
                @php
                    $cls = match($m->tipo) {
                        'entrada' => 'bg-emerald-100 text-emerald-700',
                        'secagem' => 'bg-leaf-100 text-leaf-700',
                        'ajuste'  => 'bg-amber-100 text-amber-700',
                        'saida'   => 'bg-rose-100 text-rose-700',
                        default   => 'bg-gray-100 text-gray-700',
                    };
                    $sourceLink = null;
                    if ($m->source instanceof \App\Models\Secagem) {
                        $sourceLink = ['route' => route('secagens.show', $m->source), 'label' => 'Secagem #'.$m->source->numero];
                    } elseif ($m->source instanceof \App\Models\SecagemItem && $m->source->secagem) {
                        $sourceLink = ['route' => route('secagens.show', $m->source->secagem), 'label' => 'Secagem #'.$m->source->secagem->numero];
                    }
                @endphp
                <li class="px-4 py-4">
                    <div class="flex items-start justify-between gap-3 mb-1.5">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wider {{ $cls }}">{{ \App\Support\StatusLabels::movementTipo($m->tipo) }}</span>
                            <span class="text-xs text-leaf-500">{{ $m->occurred_at->format('d/m/Y H:i') }}</span>
                        </div>
                        <span class="font-bold whitespace-nowrap text-sm {{ $m->quantidade_kg < 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                            {{ ($m->quantidade_kg > 0 ? '+' : '') }}{{ number_format($m->quantidade_kg, 3, ',', '.') }} kg
                        </span>
                    </div>
                    @if($sourceLink)
                        <a href="{{ $sourceLink['route'] }}" class="text-sm font-semibold text-leaf-700 hover:underline">{{ $sourceLink['label'] }} →</a>
                    @elseif($m->observacao)
                        <p class="text-sm text-leaf-700">{{ $m->observacao }}</p>
                    @endif
                    <div class="flex items-center justify-between text-xs text-leaf-500 mt-1.5">
                        <span>{{ $m->user?->name ? 'Por '.$m->user->name : '—' }}</span>
                        @isset($m->saldo_apos)
                            <span>Saldo após: <strong class="text-leaf-900">{{ number_format($m->saldo_apos, 3, ',', '.') }} kg</strong></span>
                        @endisset
                    </div>
                </li>
            @empty
                <li class="px-4 py-12 text-center text-leaf-500">Sem movimentações.</li>
            @endforelse
        </ul>

        {{-- Desktop: tabela --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-leaf-50/50 text-leaf-600 text-xs uppercase tracking-wider">
                    <tr>
                        <th class="text-left px-6 py-3 font-semibold">Quando</th>
                        <th class="text-left px-6 py-3 font-semibold">Tipo</th>
                        <th class="text-right px-6 py-3 font-semibold">Qtd. (kg)</th>
                        <th class="text-right px-6 py-3 font-semibold">Saldo após</th>
                        <th class="text-left px-6 py-3 font-semibold">Origem</th>
                        <th class="text-left px-6 py-3 font-semibold">Observação</th>
                        <th class="text-left px-6 py-3 font-semibold">Por</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-leaf-100">
                    @forelse($movements as $m)
                        <tr class="hover:bg-leaf-50/30 transition">
                            <td class="px-6 py-3 text-leaf-700 whitespace-nowrap">{{ $m->occurred_at->format('d/m/Y H:i') }}</td>
                            <td class="px-6 py-3">
                                @php
                                    $cls = match($m->tipo) {
                                        'entrada' => 'bg-emerald-100 text-emerald-700',
                                        'secagem' => 'bg-leaf-100 text-leaf-700',
                                        'ajuste'  => 'bg-amber-100 text-amber-700',
                                        'saida'   => 'bg-rose-100 text-rose-700',
                                        default   => 'bg-gray-100 text-gray-700',
                                    };
                                @endphp
                                <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wider {{ $cls }}">{{ \App\Support\StatusLabels::movementTipo($m->tipo) }}</span>
                            </td>
                            <td class="px-6 py-3 text-right font-bold whitespace-nowrap {{ $m->quantidade_kg < 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                                {{ ($m->quantidade_kg > 0 ? '+' : '') }}{{ number_format($m->quantidade_kg, 3, ',', '.') }}
                            </td>
                            <td class="px-6 py-3 text-right font-semibold text-leaf-900 whitespace-nowrap">
                                @isset($m->saldo_apos)
                                    {{ number_format($m->saldo_apos, 3, ',', '.') }}
                                @else
                                    —
                                @endisset
                            </td>
                            <td class="px-6 py-3 text-leaf-700">
                                @php $src = $m->source; @endphp
                                @if($src instanceof \App\Models\Secagem)
                                    <a href="{{ route('secagens.show', $src) }}"
                                       class="inline-flex items-center gap-1 text-leaf-700 font-semibold hover:underline">
                                        Secagem #{{ $src->numero }}
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                    </a>
                                @elseif($src instanceof \App\Models\SecagemItem && $src->secagem)
                                    <a href="{{ route('secagens.show', $src->secagem) }}"
                                       class="inline-flex items-center gap-1 text-leaf-700 font-semibold hover:underline">
                                        Secagem #{{ $src->secagem->numero }}
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                    </a>
                                @else
                                    <span class="text-leaf-400">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-3 text-leaf-700">{{ $m->observacao ?? '—' }}</td>
                            <td class="px-6 py-3 text-leaf-500 whitespace-nowrap">{{ $m->user?->name ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-6 py-12 text-center text-leaf-500">Sem movimentações.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $movements->links() }}</div>
</div>
@endsection
