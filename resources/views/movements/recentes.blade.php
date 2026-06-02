@extends('layouts.app')

@section('title', 'Movimentações recentes')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-leaf-900">Movimentações recentes</h1>
        <p class="text-sm text-leaf-500 mt-0.5">Tudo que entrou e saiu de estoque na fazenda, qualquer cliente, qualquer área.</p>
    </div>

    {{-- Filtros: produto + tipo --}}
    <div class="bg-white rounded-xl border border-leaf-100 shadow-sm p-3 mb-4 flex flex-col sm:flex-row sm:items-center gap-3">
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('movimentacoes.recentes', request()->except(['produto', 'page'])) }}"
               class="px-3 py-1.5 text-xs font-semibold rounded-md transition {{ ! $produto ? 'bg-leaf-700 text-white' : 'bg-white border border-leaf-200 text-leaf-700 hover:bg-leaf-50' }}">
                Todos os produtos
            </a>
            <a href="{{ route('movimentacoes.recentes', array_merge(request()->except('page'), ['produto' => 'coco'])) }}"
               class="px-3 py-1.5 text-xs font-semibold rounded-md transition {{ $produto === 'coco' ? 'bg-amber-600 text-white' : 'bg-white border border-leaf-200 text-leaf-700 hover:bg-leaf-50' }}">
                Café côco
            </a>
            <a href="{{ route('movimentacoes.recentes', array_merge(request()->except('page'), ['produto' => 'seco'])) }}"
               class="px-3 py-1.5 text-xs font-semibold rounded-md transition {{ $produto === 'seco' ? 'bg-emerald-600 text-white' : 'bg-white border border-leaf-200 text-leaf-700 hover:bg-leaf-50' }}">
                Café seco
            </a>
        </div>
        <div class="sm:ml-auto">
            <form method="GET" action="{{ route('movimentacoes.recentes') }}" class="flex items-center gap-2">
                @if($produto)<input type="hidden" name="produto" value="{{ $produto }}">@endif
                <label class="text-xs text-leaf-500">Tipo:</label>
                <select name="tipo" onchange="this.form.submit()"
                        class="px-2 py-1.5 text-xs rounded-md border border-leaf-200 bg-white focus:border-leaf-500 outline-none">
                    <option value="">Todos</option>
                    @foreach(\App\Support\StatusLabels::MOVEMENT_TIPO as $key => $label)
                        <option value="{{ $key }}" @selected($tipo === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </form>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-leaf-100 shadow-sm overflow-hidden">
        <ul class="divide-y divide-leaf-100">
            @forelse($movements as $m)
                @php
                    $tipoCls = match($m->tipo) {
                        'entrada','colheita','producao','comissao','compra' => 'bg-emerald-100 text-emerald-700',
                        'saida','secagem' => 'bg-rose-100 text-rose-700',
                        'ajuste' => 'bg-amber-100 text-amber-700',
                        default => 'bg-gray-100 text-gray-700',
                    };
                    $produtoCls = $m->produto === 'coco' ? 'bg-amber-50 text-amber-700' : 'bg-emerald-50 text-emerald-700';
                    $ownerKind = match (true) {
                        $m->owner instanceof \App\Models\Farm => 'fazenda',
                        $m->owner instanceof \App\Models\Customer => 'cliente',
                        $m->owner instanceof \App\Models\Area => 'area',
                        default => null,
                    };
                @endphp
                <li class="px-4 sm:px-6 py-4">
                    <div class="flex items-start justify-between gap-3 mb-1.5 flex-wrap">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wider {{ $tipoCls }}">{{ \App\Support\StatusLabels::movementTipo($m->tipo) }}</span>
                            <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $produtoCls }}">{{ \App\Support\StatusLabels::produto($m->produto) }}</span>
                            <span class="text-xs text-leaf-500">{{ $m->occurred_at->format('d/m/Y H:i') }}</span>
                        </div>
                        <span class="font-bold whitespace-nowrap text-sm {{ $m->quantidade_kg < 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                            {{ ($m->quantidade_kg > 0 ? '+' : '') }}{{ number_format($m->quantidade_kg, 2, ',', '.') }} kg
                        </span>
                    </div>
                    <p class="text-sm font-semibold text-leaf-900">
                        @if($ownerKind === 'fazenda')
                            <a href="{{ route('movimentacoes.fazenda.index') }}" class="hover:underline">{{ $m->owner?->nome }} (estoque da fazenda)</a>
                        @elseif($ownerKind && $m->owner)
                            <a href="{{ route('movimentacoes.index', ['tipo' => $ownerKind, 'id' => $m->owner->id]) }}" class="hover:underline">{{ $m->owner->nome }}</a>
                        @else
                            —
                        @endif
                    </p>
                    @if($m->source instanceof \App\Models\Secagem)
                        <a href="{{ route('secagens.show', $m->source) }}" class="text-xs text-leaf-700 hover:underline">Secagem #{{ $m->source->numero }} →</a>
                    @elseif($m->observacao)
                        <p class="text-xs text-leaf-700">{{ $m->observacao }}</p>
                    @endif
                    <div class="text-xs text-leaf-500 mt-1">{{ $m->user?->name ? 'Por '.$m->user->name : '—' }}</div>
                </li>
            @empty
                <li class="px-4 py-12 text-center text-leaf-500">Sem movimentações ainda.</li>
            @endforelse
        </ul>
    </div>

    <div class="mt-4">{{ $movements->links() }}</div>
</div>
@endsection
