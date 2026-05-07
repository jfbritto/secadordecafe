@extends('layouts.app')
@section('title', 'Auditoria')
@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-bold text-coffee-900">Auditoria</h1>
    <p class="text-sm text-coffee-500 mt-0.5">Histórico de alterações realizadas no sistema.</p>
</div>

<div class="bg-white rounded-xl border border-coffee-100 shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-coffee-50/50 text-coffee-600 text-xs uppercase tracking-wider">
            <tr>
                <th class="text-left px-6 py-3 font-semibold">Quando</th>
                <th class="text-left px-6 py-3 font-semibold">Por</th>
                <th class="text-left px-6 py-3 font-semibold">Descrição</th>
                <th class="text-left px-6 py-3 font-semibold">Entidade</th>
                <th class="text-left px-6 py-3 font-semibold">ID</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-coffee-100">
            @forelse($activities as $a)
                <tr class="hover:bg-coffee-50/30 transition">
                    <td class="px-6 py-3 text-coffee-700">{{ $a->created_at->format('d/m/Y H:i:s') }}</td>
                    <td class="px-6 py-3 text-coffee-700">{{ $a->causer?->name ?? 'sistema' }}</td>
                    <td class="px-6 py-3 text-coffee-900">{{ $a->description }}</td>
                    <td class="px-6 py-3 text-coffee-600">{{ class_basename($a->subject_type) }}</td>
                    <td class="px-6 py-3 text-coffee-500">#{{ $a->subject_id }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-6 py-12 text-center text-coffee-500">Sem registros.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $activities->links() }}</div>
@endsection
