@extends('layouts.app')

@section('title', 'Extrato — '.$customer->nome)

@section('content')
<div class="flex items-center justify-between mb-6 gap-4 flex-wrap">
    <div>
        <p class="text-xs text-coffee-500 mb-1">
            <a href="{{ route('clientes.index') }}" class="hover:underline">Clientes</a> ·
            <a href="{{ route('clientes.show', $customer) }}" class="hover:underline">{{ $customer->nome }}</a>
        </p>
        <h1 class="text-2xl font-bold text-coffee-900">Extrato — {{ $customer->nome }}</h1>
    </div>
    <div class="bg-coffee-700 text-white px-5 py-3 rounded-xl shadow-md text-right">
        <p class="text-[10px] uppercase tracking-wider text-coffee-200">Saldo atual</p>
        <p class="text-2xl font-bold">{{ number_format($customer->saldo_cafe_kg, 3, ',', '.') }} <span class="text-sm font-normal text-coffee-200">kg</span></p>
    </div>
</div>

<div class="bg-white rounded-xl border border-coffee-100 shadow-sm p-6 mb-6" x-data="{ tipo: 'entrada' }">
    <h2 class="text-sm font-bold text-coffee-900 uppercase tracking-wider mb-4">Nova movimentação</h2>
    <form method="POST" action="{{ route('clientes.movimentacoes.store', $customer) }}">
        @csrf
        <div class="grid sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end">
            <div>
                <label class="block text-xs font-semibold text-coffee-700 mb-1.5">Tipo</label>
                <select name="tipo" x-model="tipo" class="w-full px-3 py-2 text-sm rounded-lg border border-coffee-200 focus:outline-none focus:ring-2 focus:ring-coffee-500">
                    <option value="entrada">Entrada</option>
                    @if(auth()->user()->hasAnyRole(['admin','operador']))<option value="ajuste">Ajuste</option>@endif
                    @if(auth()->user()->hasRole('admin'))<option value="saida">Saída</option>@endif
                </select>
            </div>
            <div x-show="tipo === 'ajuste'" x-cloak>
                <label class="block text-xs font-semibold text-coffee-700 mb-1.5">Direção</label>
                <select name="direcao" class="w-full px-3 py-2 text-sm rounded-lg border border-coffee-200 focus:outline-none focus:ring-2 focus:ring-coffee-500">
                    <option value="+">+ Crédito</option>
                    <option value="-">− Débito</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-coffee-700 mb-1.5">Quantidade (kg)</label>
                <input type="number" step="0.001" min="0.001" name="quantidade" required
                       class="w-full px-3 py-2 text-sm rounded-lg border border-coffee-200 focus:outline-none focus:ring-2 focus:ring-coffee-500">
            </div>
            <div class="lg:col-span-2">
                <label class="block text-xs font-semibold text-coffee-700 mb-1.5">Observação</label>
                <input type="text" name="observacao" maxlength="500"
                       class="w-full px-3 py-2 text-sm rounded-lg border border-coffee-200 focus:outline-none focus:ring-2 focus:ring-coffee-500">
            </div>
            <div>
                <button type="submit" class="w-full px-4 py-2 text-sm font-semibold text-white bg-coffee-700 hover:bg-coffee-800 rounded-lg transition">Registrar</button>
            </div>
        </div>
        @error('tipo')<p class="mt-2 text-xs text-rose-600">{{ $message }}</p>@enderror
        @error('quantidade')<p class="mt-2 text-xs text-rose-600">{{ $message }}</p>@enderror
    </form>
</div>

<div class="bg-white rounded-xl border border-coffee-100 shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-coffee-50/50 text-coffee-600 text-xs uppercase tracking-wider">
            <tr>
                <th class="text-left px-6 py-3 font-semibold">Quando</th>
                <th class="text-left px-6 py-3 font-semibold">Tipo</th>
                <th class="text-right px-6 py-3 font-semibold">Qtd. (kg)</th>
                <th class="text-left px-6 py-3 font-semibold">Observação</th>
                <th class="text-left px-6 py-3 font-semibold">Por</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-coffee-100">
            @forelse($movements as $m)
                <tr class="hover:bg-coffee-50/30 transition">
                    <td class="px-6 py-3 text-coffee-700">{{ $m->occurred_at->format('d/m/Y H:i') }}</td>
                    <td class="px-6 py-3">
                        @php
                            $cls = match($m->tipo) {
                                'entrada' => 'bg-emerald-100 text-emerald-700',
                                'secagem' => 'bg-coffee-100 text-coffee-700',
                                'ajuste'  => 'bg-amber-100 text-amber-700',
                                'saida'   => 'bg-rose-100 text-rose-700',
                                default   => 'bg-gray-100 text-gray-700',
                            };
                        @endphp
                        <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wider {{ $cls }}">{{ $m->tipo }}</span>
                    </td>
                    <td class="px-6 py-3 text-right font-bold {{ $m->quantidade_kg < 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                        {{ ($m->quantidade_kg > 0 ? '+' : '') }}{{ number_format($m->quantidade_kg, 3, ',', '.') }}
                    </td>
                    <td class="px-6 py-3 text-coffee-700">{{ $m->observacao ?? '—' }}</td>
                    <td class="px-6 py-3 text-coffee-500">{{ $m->user?->name ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-6 py-12 text-center text-coffee-500">Sem movimentações.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $movements->links() }}</div>
@endsection
