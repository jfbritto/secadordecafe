@extends('layouts.guest')
@section('title', 'Convite já aceito')
@section('content')
<div class="bg-white rounded-2xl shadow-xl shadow-coffee-700/5 border border-coffee-100 p-8 text-center">
    <div class="w-12 h-12 rounded-full bg-emerald-100 mx-auto mb-4 flex items-center justify-center">
        <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
    </div>
    <h1 class="text-2xl font-bold text-coffee-900">Convite já aceito</h1>
    <p class="text-sm text-coffee-500 mt-2">Este convite já foi aceito em {{ $invitation->accepted_at->format('d/m/Y H:i') }}.</p>
    <a href="{{ route('login') }}" class="inline-block mt-6 px-5 py-2.5 text-sm font-semibold text-white bg-coffee-700 hover:bg-coffee-800 rounded-lg transition">Entrar</a>
</div>
@endsection
