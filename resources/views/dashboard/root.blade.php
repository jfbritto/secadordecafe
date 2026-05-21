@extends('layouts.app')

@section('title', 'Painel ROOT')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-leaf-900">Painel ROOT</h1>
    <p class="text-sm text-leaf-500 mt-0.5">Visão geral da plataforma.</p>
</div>

<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-7 gap-3 mb-6">
    @php
        $rootCards = [
            ['label'=>'Total fazendas', 'value'=>$totalFarms, 'tone'=>'coffee'],
            ['label'=>'Ativas', 'value'=>$farmsActive, 'tone'=>'emerald'],
            ['label'=>'Trial', 'value'=>$farmsTrial, 'tone'=>'amber'],
            ['label'=>'Pendentes', 'value'=>$farmsPastDue, 'tone'=>'orange'],
            ['label'=>'Bloqueadas', 'value'=>$farmsBlocked, 'tone'=>'rose'],
            ['label'=>'Usuários', 'value'=>$totalUsers, 'tone'=>'sky'],
            ['label'=>'Novos (30d)', 'value'=>$newFarms30d, 'tone'=>'indigo'],
        ];
        $tones = [
            'coffee'=>'text-leaf-700','emerald'=>'text-emerald-600','amber'=>'text-amber-600',
            'orange'=>'text-orange-600','rose'=>'text-rose-600','sky'=>'text-sky-600','indigo'=>'text-indigo-600',
        ];
    @endphp
    @foreach($rootCards as $c)
        <div class="bg-white rounded-xl border border-leaf-100 p-4 shadow-sm">
            <p class="text-[10px] font-semibold uppercase tracking-wider text-leaf-500">{{ $c['label'] }}</p>
            <p class="text-2xl font-bold mt-1 {{ $tones[$c['tone']] }}">{{ $c['value'] }}</p>
        </div>
    @endforeach
</div>

<div class="grid lg:grid-cols-2 gap-6">
    <div class="bg-white rounded-xl border border-leaf-100 shadow-sm p-6">
        <h2 class="text-sm font-bold text-leaf-900 uppercase tracking-wider mb-4">Assinaturas por status</h2>
        <ul class="space-y-2">
            @foreach(['trial'=>'Trial','active'=>'Ativas','past_due'=>'Em atraso','canceled'=>'Canceladas','blocked'=>'Bloqueadas'] as $st => $label)
                <li class="flex items-center justify-between py-2 border-b border-leaf-50 last:border-0">
                    <span class="text-sm text-leaf-700">{{ $label }}</span>
                    <span class="text-sm font-bold text-leaf-900">{{ $subscriptions[$st] ?? 0 }}</span>
                </li>
            @endforeach
        </ul>
    </div>

    <div class="bg-white rounded-xl border border-leaf-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-leaf-100">
            <h2 class="text-sm font-bold text-leaf-900 uppercase tracking-wider">Fazendas recentes</h2>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-leaf-50/50 text-leaf-600 text-xs uppercase tracking-wider">
                <tr>
                    <th class="text-left px-6 py-2 font-semibold">Fazenda</th>
                    <th class="text-left px-6 py-2 font-semibold">Status</th>
                    <th class="text-left px-6 py-2 font-semibold">Criada</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-leaf-100">
                @foreach($recentFarms as $f)
                    <tr>
                        <td class="px-6 py-2.5 text-leaf-900">{{ $f->nome }}</td>
                        <td class="px-6 py-2.5">
                            @php $cls = match($f->status){'active'=>'bg-emerald-100 text-emerald-700','blocked'=>'bg-rose-100 text-rose-700','past_due'=>'bg-amber-100 text-amber-700', default=>'bg-amber-100 text-amber-700'}; @endphp
                            <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $cls }}">{{ strtoupper($f->status) }}</span>
                        </td>
                        <td class="px-6 py-2.5 text-leaf-500">{{ $f->created_at->format('d/m/Y') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
