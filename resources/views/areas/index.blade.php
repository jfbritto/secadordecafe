@extends('layouts.app')

@section('title', 'Áreas')

@section('content')
<div class="flex items-center justify-between mb-6 gap-4 flex-wrap">
    <div>
        <h1 class="text-2xl font-bold text-leaf-900">Áreas</h1>
        <p class="text-sm text-leaf-500 mt-0.5">Talhões e lotes da sua roça. Vincule cada secagem do <strong>seu café</strong> a uma área pra acompanhar quanto cada uma produz.</p>
    </div>
    @can('create', App\Models\Area::class)
        <a href="{{ route('areas.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-white bg-leaf-700 hover:bg-leaf-800 rounded-lg transition shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Nova área
        </a>
    @endcan
</div>

<div class="bg-white rounded-xl border border-leaf-100 shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-leaf-50/50 text-leaf-600 text-xs uppercase tracking-wider">
            <tr>
                <th class="text-left px-6 py-3 font-semibold">Nome</th>
                <th class="text-left px-6 py-3 font-semibold">Localização</th>
                <th class="text-right px-6 py-3 font-semibold">Secagens</th>
                <th class="text-left px-6 py-3 font-semibold">Status</th>
                <th class="px-6 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-leaf-100">
            @forelse($areas as $a)
                <tr class="hover:bg-leaf-50/30 transition">
                    <td class="px-6 py-3 font-semibold text-leaf-900">
                        <a href="{{ route('areas.show', $a) }}" class="hover:text-leaf-700 hover:underline">{{ $a->nome }}</a>
                    </td>
                    <td class="px-6 py-3 text-leaf-600">
                        @if($a->hasLocation())
                            <a href="https://www.google.com/maps?q={{ $a->latitude }},{{ $a->longitude }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1 text-leaf-700 hover:underline">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                Ver no mapa
                            </a>
                        @else
                            <span class="text-leaf-400">—</span>
                        @endif
                    </td>
                    <td class="px-6 py-3 text-right text-leaf-700">{{ $a->secagens_count }}</td>
                    <td class="px-6 py-3">
                        @if($a->ativo)
                            <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 text-emerald-700">ATIVA</span>
                        @else
                            <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-semibold bg-gray-100 text-gray-700">INATIVA</span>
                        @endif
                    </td>
                    <td class="px-6 py-3 text-right text-sm">
                        <a href="{{ route('areas.show', $a) }}" class="inline-flex items-center gap-1 font-semibold text-leaf-700 hover:text-leaf-900 hover:underline">
                            Abrir
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-6 py-12 text-center text-leaf-500">
                    Nenhuma área cadastrada.
                    @can('create', App\Models\Area::class)
                        <a href="{{ route('areas.create') }}" class="text-leaf-700 font-semibold hover:underline">Cadastrar agora</a>.
                    @endcan
                </td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $areas->links() }}</div>
@endsection
