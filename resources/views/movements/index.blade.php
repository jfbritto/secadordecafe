@extends('layouts.app')

@section('title', 'Extrato, '.$ownerLabel)

@section('content')
@php
    $ownerIsCustomer = $owner instanceof \App\Models\Customer;
    $ownerIsArea = $owner instanceof \App\Models\Area;
    $ownerIsFarm = $owner instanceof \App\Models\Farm;

    // Area NÃO tem saldo próprio (estoque é da Farm). A view se comporta como
    // histórico de produção: lista os movements onde area_id = X, sem card de
    // saldo, sem form de movimentação manual.
    $showSaldoCard = ! $ownerIsArea;
    $showFormNova = ! $ownerIsArea;
    $saldoCoco = $ownerIsArea ? null : (float) $owner->saldo_coco_kg;
    $saldoSeco = $ownerIsArea ? null : (float) $owner->saldo_seco_kg;
    $secaoSecoLabel = 'Café seco';

    $storeUrl = $ownerIsFarm
        ? route('movimentacoes.fazenda.store')
        : route('movimentacoes.store', ['tipo' => $ownerKind, 'id' => $owner->id]);
    $baseIndexParams = $ownerIsFarm
        ? []
        : ['tipo' => $ownerKind, 'id' => $owner->id];
    $baseIndexRoute = $ownerIsFarm ? 'movimentacoes.fazenda.index' : 'movimentacoes.index';
@endphp

<div class="max-w-6xl mx-auto">
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between mb-6 gap-4">
        <div class="min-w-0">
            <p class="text-xs text-leaf-500 mb-1">
                @if($ownerIsCustomer)
                    <a href="{{ route('clientes.index') }}" class="hover:underline">Clientes</a> ·
                    <a href="{{ route('clientes.show', $owner) }}" class="hover:underline">{{ $owner->nome }}</a> ·
                @elseif($ownerIsArea)
                    <a href="{{ route('areas.index') }}" class="hover:underline">Áreas</a> ·
                    <a href="{{ route('areas.show', $owner) }}" class="hover:underline">{{ $owner->nome }}</a> ·
                @endif
                <span class="text-leaf-700">Extrato</span>
            </p>
            <h1 class="text-2xl font-bold text-leaf-900 break-words">Extrato, {{ $ownerLabel }}</h1>
        </div>
        @if($showSaldoCard)
            <div class="flex gap-2 flex-shrink-0">
                <div class="bg-amber-600 text-white px-4 py-3 rounded-xl shadow text-right">
                    <p class="text-[10px] uppercase tracking-wider text-amber-100">Café côco</p>
                    <p class="text-xl font-bold">{{ number_format($saldoCoco, 2, ',', '.') }} <span class="text-xs font-normal text-amber-100">kg</span></p>
                </div>
                <div class="bg-emerald-600 text-white px-4 py-3 rounded-xl shadow text-right">
                    <p class="text-[10px] uppercase tracking-wider text-emerald-100">{{ $secaoSecoLabel }}</p>
                    <p class="text-xl font-bold">{{ number_format($saldoSeco, 2, ',', '.') }} <span class="text-xs font-normal text-emerald-100">kg</span></p>
                </div>
            </div>
        @endif
    </div>

    {{-- Filtro por produto --}}
    <div class="flex gap-2 mb-4 flex-wrap">
        <a href="{{ route($baseIndexRoute, $baseIndexParams) }}"
           class="px-3 py-1.5 text-xs font-semibold rounded-md transition {{ ! $produto ? 'bg-leaf-700 text-white' : 'bg-white border border-leaf-200 text-leaf-700 hover:bg-leaf-50' }}">
            Todos os produtos
        </a>
        <a href="{{ route($baseIndexRoute, $baseIndexParams + ['produto' => 'coco']) }}"
           class="px-3 py-1.5 text-xs font-semibold rounded-md transition {{ $produto === 'coco' ? 'bg-amber-600 text-white' : 'bg-white border border-leaf-200 text-leaf-700 hover:bg-leaf-50' }}">
            Só café côco
        </a>
        <a href="{{ route($baseIndexRoute, $baseIndexParams + ['produto' => 'seco']) }}"
           class="px-3 py-1.5 text-xs font-semibold rounded-md transition {{ $produto === 'seco' ? 'bg-emerald-600 text-white' : 'bg-white border border-leaf-200 text-leaf-700 hover:bg-leaf-50' }}">
            Só café seco
        </a>
    </div>

    {{-- Form de nova movimentação manual (Area não tem; veja Colheita) --}}
    @if($showFormNova)
    @can('create', [\App\Models\Movement::class, 'entrada'])
    <div class="bg-white rounded-2xl border border-leaf-100 shadow-sm p-6 mb-6" x-data="{ tipo: 'entrada' }">
        <div class="border-b border-leaf-100 pb-4 mb-5">
            <h2 class="text-base font-bold text-leaf-900">Nova movimentação</h2>
            <p class="text-sm text-leaf-500 mt-0.5">Entrada, saída avulsa ou ajuste de saldo.</p>
        </div>

        <form method="POST" action="{{ $storeUrl }}">
            @csrf
            <div class="grid sm:grid-cols-12 gap-4 items-end">
                <div class="sm:col-span-3">
                    <label class="block text-sm font-bold text-leaf-900 mb-2">Tipo <span class="text-rose-500">*</span></label>
                    <select name="tipo" x-model="tipo"
                            class="w-full px-3 py-2.5 text-sm rounded-lg border border-leaf-200 focus:border-leaf-500 focus:ring-2 focus:ring-leaf-500/15 outline-none bg-white">
                        <option value="entrada">+ Entrada</option>
                        @if(auth()->user()->hasAnyRole(['admin','operador']))
                            <option value="ajuste">± Ajuste</option>
                        @endif
                        @if(auth()->user()->hasRole('admin'))
                            <option value="saida">− Saída</option>
                        @endif
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-bold text-leaf-900 mb-2">Produto <span class="text-rose-500">*</span></label>
                    <select name="produto" class="w-full px-3 py-2.5 text-sm rounded-lg border border-leaf-200 focus:border-leaf-500 focus:ring-2 focus:ring-leaf-500/15 outline-none bg-white">
                        <option value="coco">Café côco</option>
                        <option value="seco">Café seco</option>
                    </select>
                </div>
                <div class="sm:col-span-2" x-show="tipo === 'ajuste'" x-cloak>
                    <label class="block text-sm font-bold text-leaf-900 mb-2">Direção</label>
                    <select name="direcao" class="w-full px-3 py-2.5 text-sm rounded-lg border border-leaf-200 focus:border-leaf-500 focus:ring-2 focus:ring-leaf-500/15 outline-none bg-white">
                        <option value="+">+ Soma</option>
                        <option value="-">− Subtrai</option>
                    </select>
                </div>
                <div class="sm:col-span-4">
                    <label class="block text-sm font-bold text-leaf-900 mb-2">Quantidade <span class="text-rose-500">*</span> <span class="text-leaf-400 font-normal text-xs">(kg ou sacos)</span></label>
                    <x-input-quantidade name="quantidade" :required="true" />
                </div>
                <div class="sm:col-span-3">
                    <label class="block text-sm font-bold text-leaf-900 mb-2">Observação</label>
                    <input type="text" name="observacao" maxlength="500" placeholder="Opcional"
                           class="w-full px-3 py-3 text-sm rounded-lg border border-leaf-200 focus:border-leaf-500 focus:ring-2 focus:ring-leaf-500/15 outline-none">
                </div>
                <div class="sm:col-span-12">
                    <button class="px-5 py-2.5 text-sm font-bold text-white bg-leaf-700 hover:bg-leaf-800 rounded-lg transition">Registrar</button>
                </div>
            </div>
            @error('quantidade')<p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>@enderror
        </form>
    </div>
    @endcan
    @endif

    {{-- Extrato --}}
    <div class="bg-white rounded-2xl border border-leaf-100 shadow-sm overflow-hidden">
        <ul class="divide-y divide-leaf-100">
            @forelse($movements as $m)
                @php
                    $tipoCls = match($m->tipo) {
                        'entrada','colheita','producao','comissao' => 'bg-emerald-100 text-emerald-700',
                        'saida','secagem' => 'bg-rose-100 text-rose-700',
                        'ajuste' => 'bg-amber-100 text-amber-700',
                        default => 'bg-gray-100 text-gray-700',
                    };
                    $produtoCls = $m->produto === 'coco' ? 'bg-amber-50 text-amber-700' : 'bg-emerald-50 text-emerald-700';
                @endphp
                <li class="px-4 sm:px-6 py-4">
                    <div class="flex items-start justify-between gap-3 mb-1.5">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wider {{ $tipoCls }}">{{ \App\Support\StatusLabels::movementTipo($m->tipo) }}</span>
                            <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $produtoCls }}">{{ \App\Support\StatusLabels::produto($m->produto) }}</span>
                            <span class="text-xs text-leaf-500">{{ $m->occurred_at->format('d/m/Y H:i') }}</span>
                        </div>
                        <span class="font-bold whitespace-nowrap text-sm {{ $m->quantidade_kg < 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                            {{ ($m->quantidade_kg > 0 ? '+' : '') }}{{ number_format($m->quantidade_kg, 2, ',', '.') }} kg
                        </span>
                    </div>
                    @if($m->source instanceof \App\Models\Secagem)
                        <a href="{{ route('secagens.show', $m->source) }}" class="text-sm font-semibold text-leaf-700 hover:underline">Secagem #{{ $m->source->numero }} →</a>
                    @elseif($m->observacao)
                        <p class="text-sm text-leaf-700">{{ $m->observacao }}</p>
                    @endif
                    <div class="flex items-center justify-between text-xs text-leaf-500 mt-1.5">
                        <span>{{ $m->user?->name ? 'Por '.$m->user->name : '—' }}</span>
                        @isset($m->saldo_apos)
                            <span>Saldo após: <strong class="text-leaf-900">{{ number_format($m->saldo_apos, 2, ',', '.') }} kg</strong></span>
                        @endisset
                    </div>
                </li>
            @empty
                <li class="px-4 py-12 text-center text-leaf-500">Sem movimentações.</li>
            @endforelse
        </ul>
    </div>

    <div class="mt-4">{{ $movements->links() }}</div>
</div>
@endsection
