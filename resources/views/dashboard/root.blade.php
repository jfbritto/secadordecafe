@extends('layouts.app')

@section('title', 'Painel ROOT')

@section('content')
<div class="flex items-start justify-between mb-6 gap-4 flex-wrap">
    <div>
        <h1 class="text-2xl font-bold text-leaf-900">Painel ROOT</h1>
        <p class="text-sm text-leaf-500 mt-0.5">Visão geral da plataforma.</p>
    </div>
    <a href="{{ route('admin.fazendas.index') }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-leaf-700 hover:bg-leaf-800 rounded-lg transition shadow-sm">
        Ver todas as fazendas
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
    </a>
</div>

<div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-3 mb-6">
    @php
        $rootCards = [
            ['label'=>'Total fazendas', 'value'=>$totalFarms, 'tone'=>'coffee'],
            ['label'=>'Ativas', 'value'=>$farmsActive, 'tone'=>'emerald'],
            ['label'=>'Parceiras', 'value'=>$farmsPartner ?? 0, 'tone'=>'purple'],
            ['label'=>'Trial', 'value'=>$farmsTrial, 'tone'=>'amber'],
            ['label'=>'Pendentes', 'value'=>$farmsPastDue, 'tone'=>'orange'],
            ['label'=>'Bloqueadas', 'value'=>$farmsBlocked, 'tone'=>'rose'],
            ['label'=>'Usuários', 'value'=>$totalUsers, 'tone'=>'sky'],
            ['label'=>'Novos (30d)', 'value'=>$newFarms30d, 'tone'=>'indigo'],
        ];
        $tones = [
            'coffee'=>'text-leaf-700','emerald'=>'text-emerald-600','amber'=>'text-amber-600',
            'orange'=>'text-orange-600','rose'=>'text-rose-600','sky'=>'text-sky-600','indigo'=>'text-indigo-600',
            'purple'=>'text-purple-600',
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
            @foreach(['trial'=>'Trial','active'=>'Ativas','partner'=>'Parceiras','past_due'=>'Em atraso','canceled'=>'Canceladas','blocked'=>'Bloqueadas'] as $st => $label)
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
                    <tr class="hover:bg-leaf-50/30 transition">
                        <td class="px-6 py-2.5">
                            <a href="{{ route('admin.fazendas.show', $f) }}" class="text-leaf-900 font-semibold hover:underline">{{ $f->nome }}</a>
                        </td>
                        <td class="px-6 py-2.5">
                            @php $cls = match($f->status){
                                'active'=>'bg-emerald-100 text-emerald-700',
                                'partner'=>'bg-purple-100 text-purple-700',
                                'blocked'=>'bg-rose-100 text-rose-700',
                                'past_due'=>'bg-orange-100 text-orange-700',
                                'canceled'=>'bg-gray-100 text-gray-700',
                                default=>'bg-amber-100 text-amber-700'
                            }; @endphp
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
