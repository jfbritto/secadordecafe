@extends('layouts.app')

@section('title', 'Fazendas (ROOT)')

@section('content')
<div class="mb-6">
    <div>
        <p class="text-xs text-leaf-500 mb-1"><span class="text-amber-700 font-semibold">Painel da Plataforma</span></p>
        <h1 class="text-2xl font-bold text-leaf-900">Fazendas da plataforma</h1>
        <p class="text-sm text-leaf-500 mt-0.5">Visão global de todas as roças. Clica em qualquer uma pra ver detalhes e mudar plano.</p>
    </div>
</div>

{{-- Filtros --}}
<form method="GET" class="bg-white rounded-xl border border-leaf-100 p-4 shadow-sm mb-4">
    <div class="grid sm:grid-cols-12 gap-3">
        <div class="sm:col-span-7">
            <label class="block text-[10px] uppercase tracking-wider text-leaf-500 font-semibold mb-1.5">Buscar</label>
            <input type="text" name="q" value="{{ $term }}"
                   placeholder="Nome da fazenda ou e-mail do admin"
                   class="w-full px-3 py-2 text-sm rounded-lg border border-leaf-200 focus:outline-none focus:ring-2 focus:ring-leaf-500 focus:border-leaf-500">
        </div>
        <div class="sm:col-span-3">
            <label class="block text-[10px] uppercase tracking-wider text-leaf-500 font-semibold mb-1.5">Plano</label>
            <select name="status"
                    class="w-full px-3 py-2 text-sm rounded-lg border border-leaf-200 focus:outline-none focus:ring-2 focus:ring-leaf-500 focus:border-leaf-500 bg-white">
                <option value="">— todos —</option>
                @foreach($statuses as $value => $label)
                    <option value="{{ $value }}" @selected($statusFilter === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="sm:col-span-2 flex items-end">
            <button type="submit" class="w-full px-5 py-2 text-sm font-semibold text-white bg-leaf-700 hover:bg-leaf-800 rounded-lg transition">Buscar</button>
        </div>
    </div>
</form>

<div class="bg-white rounded-xl border border-leaf-100 shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-leaf-50/50 text-leaf-600 text-xs uppercase tracking-wider">
            <tr>
                <th class="text-left px-6 py-3 font-semibold">Fazenda</th>
                <th class="text-left px-6 py-3 font-semibold">Admin</th>
                <th class="text-left px-6 py-3 font-semibold">Plano</th>
                <th class="text-right px-6 py-3 font-semibold">Usuários</th>
                <th class="text-right px-6 py-3 font-semibold">Clientes</th>
                <th class="text-left px-6 py-3 font-semibold">Criada</th>
                <th class="px-6 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-leaf-100">
            @forelse($farms as $farm)
                @php
                    $owner = $farm->users->first();
                    $statusValue = $farm->subscription?->status ?? $farm->status;
                    $tone = match($statusValue) {
                        'partner'   => 'bg-purple-100 text-purple-700',
                        'active'    => 'bg-emerald-100 text-emerald-700',
                        'trial'     => 'bg-amber-100 text-amber-700',
                        'past_due'  => 'bg-orange-100 text-orange-700',
                        'blocked'   => 'bg-rose-100 text-rose-700',
                        'canceled'  => 'bg-gray-100 text-gray-700',
                        default     => 'bg-gray-100 text-gray-700',
                    };
                @endphp
                <tr class="hover:bg-leaf-50/30 transition">
                    <td class="px-6 py-3">
                        <a href="{{ route('admin.fazendas.show', $farm) }}" class="font-semibold text-leaf-900 hover:underline">{{ $farm->nome }}</a>
                        @if($farm->cidade)
                            <p class="text-xs text-leaf-500">{{ $farm->cidade }}{{ $farm->estado ? '/' . $farm->estado : '' }}</p>
                        @endif
                    </td>
                    <td class="px-6 py-3">
                        @if($owner)
                            <p class="text-leaf-900">{{ $owner->name }}</p>
                            <p class="text-xs text-leaf-500">{{ $owner->email }}</p>
                        @else
                            <span class="text-leaf-400">—</span>
                        @endif
                    </td>
                    <td class="px-6 py-3">
                        <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $tone }}">{{ $statuses[$statusValue] ?? $statusValue }}</span>
                    </td>
                    <td class="px-6 py-3 text-right text-leaf-700">{{ $farm->users_count }}</td>
                    <td class="px-6 py-3 text-right text-leaf-700">{{ $farm->customers_count }}</td>
                    <td class="px-6 py-3 text-leaf-600">{{ $farm->created_at->format('d/m/Y') }}</td>
                    <td class="px-6 py-3 text-right text-sm">
                        <a href="{{ route('admin.fazendas.show', $farm) }}" class="inline-flex items-center gap-1 font-semibold text-leaf-700 hover:text-leaf-900 hover:underline">
                            Abrir
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-6 py-12 text-center text-leaf-500">Nenhuma fazenda encontrada.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $farms->links() }}</div>
@endsection
