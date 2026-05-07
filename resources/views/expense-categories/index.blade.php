@extends('layouts.app')

@section('title', 'Categorias de despesa')

@section('content')
<div class="flex items-center justify-between mb-6 gap-4 flex-wrap">
    <div>
        <p class="text-xs text-coffee-500 mb-1"><a href="{{ route('despesas.index') }}" class="hover:underline">Despesas</a></p>
        <h1 class="text-2xl font-bold text-coffee-900">Categorias de despesa</h1>
        <p class="text-sm text-coffee-500 mt-0.5">Organize seus lançamentos financeiros por categoria.</p>
    </div>
    @can('create', App\Models\ExpenseCategory::class)
        <a href="{{ route('despesas.categorias.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-white bg-coffee-700 hover:bg-coffee-800 rounded-lg transition shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Nova categoria
        </a>
    @endcan
</div>

<div class="bg-white rounded-xl border border-coffee-100 shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-coffee-50/50 text-coffee-600 text-xs uppercase tracking-wider">
            <tr>
                <th class="text-left px-6 py-3 font-semibold">Nome</th>
                <th class="text-right px-6 py-3 font-semibold">Lançamentos</th>
                <th class="text-left px-6 py-3 font-semibold">Status</th>
                <th class="px-6 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-coffee-100">
            @forelse($categories as $cat)
                <tr class="hover:bg-coffee-50/30 transition">
                    <td class="px-6 py-3 font-semibold text-coffee-900">{{ $cat->nome }}</td>
                    <td class="px-6 py-3 text-right text-coffee-700">{{ $cat->expenses_count }}</td>
                    <td class="px-6 py-3">
                        @if($cat->ativo)
                            <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 text-emerald-700">ATIVA</span>
                        @else
                            <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-semibold bg-gray-100 text-gray-700">INATIVA</span>
                        @endif
                    </td>
                    <td class="px-6 py-3 text-right text-sm">
                        @can('update', $cat)
                            <a href="{{ route('despesas.categorias.edit', $cat) }}" class="text-coffee-600 hover:text-coffee-900 hover:underline">editar</a>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="px-6 py-12 text-center text-coffee-500">Nenhuma categoria cadastrada.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $categories->links() }}</div>
@endsection
