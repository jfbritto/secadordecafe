@extends('layouts.app')

@section('title', 'Secadores')

@section('content')
<div class="flex items-center justify-between mb-6 gap-4 flex-wrap">
    <div>
        <h1 class="text-2xl font-bold text-leaf-900">Secadores</h1>
        <p class="text-sm text-leaf-500 mt-0.5">Equipamentos de secagem da fazenda.</p>
    </div>
    @can('create', App\Models\Dryer::class)
        <a href="{{ route('secadores.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-white bg-leaf-700 hover:bg-leaf-800 rounded-lg transition shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Novo secador
        </a>
    @endcan
</div>

<div class="bg-white rounded-xl border border-leaf-100 shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-leaf-50/50 text-leaf-600 text-xs uppercase tracking-wider">
            <tr>
                <th class="text-left px-6 py-3 font-semibold">Nome</th>
                <th class="text-left px-6 py-3 font-semibold">Modelo</th>
                <th class="text-right px-6 py-3 font-semibold">Capacidade</th>
                <th class="text-right px-6 py-3 font-semibold">Secagens</th>
                <th class="text-left px-6 py-3 font-semibold">Status</th>
                <th class="px-6 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-leaf-100">
            @forelse($dryers as $d)
                <tr class="hover:bg-leaf-50/30 transition">
                    <td class="px-6 py-3 font-semibold text-leaf-900">{{ $d->nome }}</td>
                    <td class="px-6 py-3 text-leaf-600">{{ $d->modelo ?? '—' }}</td>
                    <td class="px-6 py-3 text-right text-leaf-700">
                        {{ $d->capacidade_kg ? number_format($d->capacidade_kg, 0, ',', '.') . ' kg' : '—' }}
                    </td>
                    <td class="px-6 py-3 text-right text-leaf-700">{{ $d->secagens_count }}</td>
                    <td class="px-6 py-3">
                        @if($d->ativo)
                            <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 text-emerald-700">ATIVO</span>
                        @else
                            <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-semibold bg-gray-100 text-gray-700">INATIVO</span>
                        @endif
                    </td>
                    <td class="px-6 py-3 text-right text-sm">
                        @can('update', $d)
                            <a href="{{ route('secadores.edit', $d) }}" class="text-leaf-600 hover:text-leaf-900 hover:underline">editar</a>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-6 py-12 text-center text-leaf-500">
                    Nenhum secador cadastrado.
                    @can('create', App\Models\Dryer::class)
                        <a href="{{ route('secadores.create') }}" class="text-leaf-700 font-semibold hover:underline">Cadastrar agora</a>.
                    @endcan
                </td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $dryers->links() }}</div>
@endsection
