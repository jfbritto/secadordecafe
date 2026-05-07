@extends('layouts.app')

@section('title', 'Acesso bloqueado')

@section('content')
<div class="max-w-2xl mx-auto bg-white rounded-2xl shadow border border-coffee-100 p-10 text-center">
    <div class="w-14 h-14 rounded-full bg-rose-100 mx-auto mb-5 flex items-center justify-center">
        <svg class="w-7 h-7 text-rose-600" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
    </div>
    <h1 class="text-3xl font-bold text-coffee-900">Acesso bloqueado</h1>
    <p class="text-coffee-600 mt-3">A fazenda <strong class="text-coffee-900">{{ $farm?->nome ?? '—' }}</strong> está com o acesso temporariamente bloqueado.</p>
    <p class="text-sm text-coffee-500 mt-2">Isso normalmente ocorre por inadimplência ou bloqueio administrativo. Entre em contato com o suporte para regularizar.</p>
    <form method="POST" action="{{ route('logout') }}" class="mt-6">
        @csrf
        <button type="submit" class="px-6 py-2.5 text-sm font-semibold text-white bg-coffee-700 hover:bg-coffee-800 rounded-lg transition">Sair</button>
    </form>
</div>
@endsection
