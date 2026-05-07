@extends('layouts.guest')
@section('title', 'Convite expirado')
@section('content')
<div class="bg-white rounded-2xl shadow-xl shadow-coffee-700/5 border border-coffee-100 p-8 text-center">
    <div class="w-12 h-12 rounded-full bg-amber-100 mx-auto mb-4 flex items-center justify-center">
        <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    </div>
    <h1 class="text-2xl font-bold text-coffee-900">Convite expirado</h1>
    <p class="text-sm text-coffee-500 mt-2">Este convite expirou em {{ $invitation->expires_at->format('d/m/Y H:i') }}.</p>
    <p class="text-sm text-coffee-600 mt-3">
        Entre em contato com a administradora da fazenda <strong>{{ $invitation->farm?->nome }}</strong>
        para receber um novo convite.
    </p>
    <a href="{{ route('login') }}" class="inline-block mt-6 text-sm font-semibold text-coffee-700 hover:underline">Ir para login</a>
</div>
@endsection
