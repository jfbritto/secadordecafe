@extends('layouts.app')

@section('title', 'Secagens')

@section('content')
<div class="flex items-center justify-between mb-6 gap-4 flex-wrap">
    <div>
        <h1 class="text-2xl font-bold text-leaf-900">Secagens</h1>
        <p class="text-sm text-leaf-500 mt-0.5">Operações de secagem com cálculo automático de rendimento e comissão.</p>
    </div>
    @can('create', App\Models\Secagem::class)
        <a href="{{ route('secagens.create') }}" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-semibold text-white bg-leaf-700 hover:bg-leaf-800 rounded-lg transition shadow-sm w-full sm:w-auto">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Nova secagem
        </a>
    @endcan
</div>

<div class="bg-white rounded-xl border border-leaf-100 shadow-sm overflow-hidden">
    {{-- Mobile: cards --}}
    <ul class="md:hidden divide-y divide-leaf-100">
        @forelse($secagens as $s)
            <li>
                <a href="{{ $s->isRascunho() && auth()->user()->can('update', $s) ? route('secagens.edit', $s) : route('secagens.show', $s) }}"
                   class="flex items-center gap-3 px-4 py-4 hover:bg-leaf-50/30 transition">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <p class="font-bold text-leaf-900">#{{ $s->numero }}</p>
                            @if($s->referencia())
                                <p class="font-semibold text-leaf-800 truncate">{{ $s->referencia() }}</p>
                            @endif
                            @if($s->isConcluida())
                                <span class="inline-block px-1.5 py-0.5 rounded text-[9px] font-semibold bg-emerald-100 text-emerald-700 flex-shrink-0">CONCLUÍDA</span>
                            @else
                                <span class="inline-block px-1.5 py-0.5 rounded text-[9px] font-semibold bg-amber-100 text-amber-700 flex-shrink-0">RASCUNHO</span>
                            @endif
                        </div>
                        <p class="text-sm text-leaf-700 truncate">{{ $s->data->format('d/m/Y') }} · {{ $s->secadorNome() }}</p>
                        <p class="text-xs text-leaf-500 mt-0.5">{{ $s->items_count }} {{ $s->items_count === 1 ? 'item' : 'itens' }}</p>
                    </div>
                    <svg class="w-4 h-4 text-leaf-400 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </a>
            </li>
        @empty
            <li class="px-4 py-12 text-center text-leaf-500">Nenhuma secagem registrada.</li>
        @endforelse
    </ul>

    {{-- Desktop: tabela --}}
    <table class="hidden md:table w-full text-sm">
        <thead class="bg-leaf-50/50 text-leaf-600 text-xs uppercase tracking-wider">
            <tr>
                <th class="text-left px-6 py-3 font-semibold">#</th>
                <th class="text-left px-6 py-3 font-semibold">Referência</th>
                <th class="text-left px-6 py-3 font-semibold">Data</th>
                <th class="text-left px-6 py-3 font-semibold">Secador</th>
                <th class="text-right px-6 py-3 font-semibold">Itens</th>
                <th class="text-left px-6 py-3 font-semibold">Status</th>
                <th class="px-6 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-leaf-100">
            @forelse($secagens as $s)
                <tr class="hover:bg-leaf-50/30 transition">
                    <td class="px-6 py-3 font-bold text-leaf-700"><a href="{{ route('secagens.show', $s) }}" class="hover:underline">#{{ $s->numero }}</a></td>
                    <td class="px-6 py-3 text-leaf-800 font-medium">{{ $s->referencia() ?? '—' }}</td>
                    <td class="px-6 py-3 text-leaf-700">{{ $s->data->format('d/m/Y') }}</td>
                    <td class="px-6 py-3 text-leaf-700">{{ $s->secadorNome() }}</td>
                    <td class="px-6 py-3 text-right text-leaf-700">{{ $s->items_count }}</td>
                    <td class="px-6 py-3">
                        @if($s->isConcluida())
                            <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 text-emerald-700">CONCLUÍDA</span>
                        @else
                            <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-100 text-amber-700">RASCUNHO</span>
                        @endif
                    </td>
                    <td class="px-6 py-3 text-right text-sm">
                        @if($s->isRascunho())
                            @can('update', $s)<a href="{{ route('secagens.edit', $s) }}" class="text-leaf-600 hover:text-leaf-900 hover:underline">editar</a>@endcan
                        @else
                            <a href="{{ route('secagens.show', $s) }}" class="text-leaf-600 hover:text-leaf-900 hover:underline">ver</a>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-6 py-12 text-center text-leaf-500">Nenhuma secagem registrada.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $secagens->links() }}</div>
@endsection
