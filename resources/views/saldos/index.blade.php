@extends('layouts.app')

@php
    $produtoLabel = \App\Support\StatusLabels::produto($produto);
    $tituloPagina = $incluiFazenda
        ? "Estoque total, {$produtoLabel}"
        : "Estoque de terceiros, {$produtoLabel}";
@endphp

@section('title', $tituloPagina)

@section('content')
<div class="mb-6">
    <p class="text-xs text-leaf-500 mb-1"><a href="{{ route('dashboard') }}" class="hover:underline">Dashboard</a></p>
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-leaf-900">{{ $tituloPagina }}</h1>
            <p class="text-sm text-leaf-500 mt-0.5">
                @if($incluiFazenda)
                    Estoque próprio da fazenda mais os saldos de cada cliente.
                @else
                    Café guardado em nome de cada cliente.
                @endif
            </p>
        </div>
        <div class="inline-flex rounded-lg border border-leaf-200 bg-white p-1 self-start sm:self-auto">
            @php
                $rota = $incluiFazenda ? 'saldos.geral' : 'saldos.clientes';
            @endphp
            <a href="{{ route($rota, ['produto' => 'coco']) }}"
               class="px-3 py-1.5 text-xs font-semibold rounded-md transition {{ $produto === 'coco' ? 'bg-amber-100 text-amber-700' : 'text-leaf-500 hover:text-leaf-900' }}">Café côco</a>
            <a href="{{ route($rota, ['produto' => 'seco']) }}"
               class="px-3 py-1.5 text-xs font-semibold rounded-md transition {{ $produto === 'seco' ? 'bg-emerald-100 text-emerald-700' : 'text-leaf-500 hover:text-leaf-900' }}">Café seco</a>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-6">
    @if($incluiFazenda)
        <div class="bg-white rounded-xl border border-leaf-100 p-4 shadow-sm">
            <p class="text-xs uppercase tracking-wider text-leaf-500">Próprio (fazenda)</p>
            <p class="text-xl font-bold text-leaf-900 mt-1">{{ number_format($totalFazenda, 2, ',', '.') }} kg</p>
            <p class="text-xs text-leaf-400">{{ \App\Support\Sacos::formatSacos($totalFazenda) }}</p>
        </div>
    @endif
    <div class="bg-white rounded-xl border border-leaf-100 p-4 shadow-sm">
        <p class="text-xs uppercase tracking-wider text-leaf-500">Terceiros (clientes)</p>
        <p class="text-xl font-bold text-leaf-900 mt-1">{{ number_format($totalClientes, 2, ',', '.') }} kg</p>
        <p class="text-xs text-leaf-400">{{ \App\Support\Sacos::formatSacos($totalClientes) }}</p>
    </div>
    <div class="bg-leaf-700 text-white rounded-xl p-4 shadow-sm {{ ! $incluiFazenda ? 'sm:col-span-2' : '' }}">
        <p class="text-xs uppercase tracking-wider text-leaf-200">{{ $incluiFazenda ? 'Total' : 'Total terceiros' }}</p>
        <p class="text-xl font-bold mt-1">{{ number_format($incluiFazenda ? $totalGeral : $totalClientes, 2, ',', '.') }} kg</p>
        <p class="text-xs text-leaf-200">{{ \App\Support\Sacos::formatSacos($incluiFazenda ? $totalGeral : $totalClientes) }}</p>
    </div>
</div>

@if($clientes->isEmpty() && ! ($incluiFazenda && $fazenda && (float) $fazenda->{$coluna} > 0))
    <div class="bg-white rounded-xl border border-leaf-100 shadow-sm p-8 text-center">
        <p class="text-leaf-500">Nenhum saldo de {{ mb_strtolower($produtoLabel) }} no momento.</p>
    </div>
@else
    <div class="bg-white rounded-xl border border-leaf-100 shadow-sm overflow-hidden">
        {{-- Mobile: cards --}}
        <ul class="md:hidden divide-y divide-leaf-100">
            @if($incluiFazenda && $fazenda && (float) $fazenda->{$coluna} > 0)
                @php $saldo = (float) $fazenda->{$coluna}; @endphp
                <li>
                    <a href="{{ route('movimentacoes.fazenda.index', ['produto' => $produto]) }}" class="block px-4 py-4 hover:bg-leaf-50/50 transition">
                        <div class="flex items-center justify-between gap-3">
                            <div class="min-w-0 flex items-center gap-2">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-leaf-100 text-leaf-700">Próprio</span>
                                <p class="text-sm font-semibold text-leaf-900 truncate">{{ $fazenda->nome }}</p>
                            </div>
                            <span class="text-leaf-400">→</span>
                        </div>
                        <div class="mt-1.5 flex items-baseline gap-2">
                            <p class="text-base font-bold text-leaf-900">{{ number_format($saldo, 2, ',', '.') }} kg</p>
                            <p class="text-xs text-leaf-400">{{ \App\Support\Sacos::formatSacos($saldo) }}</p>
                        </div>
                    </a>
                </li>
            @endif
            @foreach($clientes as $cliente)
                @php $saldo = (float) $cliente->{$coluna}; @endphp
                <li>
                    <a href="{{ route('movimentacoes.index', ['tipo' => 'cliente', 'id' => $cliente->id, 'produto' => $produto]) }}" class="block px-4 py-4 hover:bg-leaf-50/50 transition">
                        <div class="flex items-center justify-between gap-3">
                            <p class="text-sm font-semibold text-leaf-900 truncate">{{ $cliente->nome }}</p>
                            <span class="text-leaf-400">→</span>
                        </div>
                        <div class="mt-1.5 flex items-baseline gap-2">
                            <p class="text-base font-bold text-leaf-900">{{ number_format($saldo, 2, ',', '.') }} kg</p>
                            <p class="text-xs text-leaf-400">{{ \App\Support\Sacos::formatSacos($saldo) }}</p>
                        </div>
                    </a>
                </li>
            @endforeach
        </ul>

        {{-- Desktop: tabela --}}
        <table class="hidden md:table w-full text-sm">
            <thead class="bg-leaf-50/50 text-leaf-600 text-xs uppercase tracking-wider">
                <tr>
                    <th class="text-left px-4 py-3 font-semibold">Origem</th>
                    <th class="text-right px-4 py-3 font-semibold">Saldo</th>
                    <th class="text-right px-4 py-3 font-semibold">Sacos</th>
                    <th class="text-right px-4 py-3 font-semibold w-32">Extrato</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-leaf-100">
                @if($incluiFazenda && $fazenda && (float) $fazenda->{$coluna} > 0)
                    @php $saldo = (float) $fazenda->{$coluna}; @endphp
                    <tr class="hover:bg-leaf-50/30 transition bg-leaf-50/20">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-leaf-100 text-leaf-700">Próprio</span>
                                <span class="font-semibold text-leaf-900">{{ $fazenda->nome }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-right font-bold text-leaf-900">{{ number_format($saldo, 2, ',', '.') }} kg</td>
                        <td class="px-4 py-3 text-right text-leaf-500">{{ \App\Support\Sacos::formatSacos($saldo) }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('movimentacoes.fazenda.index', ['produto' => $produto]) }}" class="text-leaf-700 font-semibold hover:underline">Ver →</a>
                        </td>
                    </tr>
                @endif
                @foreach($clientes as $cliente)
                    @php $saldo = (float) $cliente->{$coluna}; @endphp
                    <tr class="hover:bg-leaf-50/30 transition">
                        <td class="px-4 py-3 text-leaf-900 font-medium">{{ $cliente->nome }}</td>
                        <td class="px-4 py-3 text-right font-bold text-leaf-900">{{ number_format($saldo, 2, ',', '.') }} kg</td>
                        <td class="px-4 py-3 text-right text-leaf-500">{{ \App\Support\Sacos::formatSacos($saldo) }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('movimentacoes.index', ['tipo' => 'cliente', 'id' => $cliente->id, 'produto' => $produto]) }}" class="text-leaf-700 font-semibold hover:underline">Ver →</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
@endsection
