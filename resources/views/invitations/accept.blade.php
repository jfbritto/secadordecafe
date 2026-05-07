@extends('layouts.guest')

@section('title', 'Aceitar convite')

@section('content')
<div class="bg-white rounded-2xl shadow-xl shadow-coffee-700/5 border border-coffee-100 p-8">
    <h1 class="text-2xl font-bold text-coffee-900">Aceitar convite</h1>
    <p class="text-sm text-coffee-500 mt-1 mb-5">
        Você foi convidado para <strong class="text-coffee-800">{{ $invitation->farm?->nome }}</strong>
        como <strong class="text-coffee-800">{{ ucfirst($invitation->role) }}</strong>.
    </p>

    @if(session('error'))
        <div class="mb-4 px-4 py-3 rounded-lg bg-rose-50 border border-rose-200 text-rose-800 text-sm">{{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('convite.store', ['token' => $invitation->token]) }}" class="space-y-4" novalidate>
        @csrf

        <div>
            <label class="block text-sm font-semibold text-coffee-800 mb-1.5">E-mail</label>
            <input type="email" value="{{ $invitation->email }}" disabled
                   class="w-full px-3.5 py-2.5 text-sm rounded-lg border border-coffee-200 bg-coffee-50 text-coffee-600 cursor-not-allowed">
        </div>

        <div>
            <label for="name" class="block text-sm font-semibold text-coffee-800 mb-1.5">Seu nome</label>
            <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus
                   class="w-full px-3.5 py-2.5 text-sm rounded-lg border border-coffee-200 focus:outline-none focus:ring-2 focus:ring-coffee-500 focus:border-coffee-500">
            @error('name')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
        </div>

        <div class="grid grid-cols-2 gap-3">
            <div>
                <label for="password" class="block text-sm font-semibold text-coffee-800 mb-1.5">Senha</label>
                <input id="password" name="password" type="password" required
                       class="w-full px-3.5 py-2.5 text-sm rounded-lg border border-coffee-200 focus:outline-none focus:ring-2 focus:ring-coffee-500 focus:border-coffee-500">
                @error('password')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="password_confirmation" class="block text-sm font-semibold text-coffee-800 mb-1.5">Confirmar</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required
                       class="w-full px-3.5 py-2.5 text-sm rounded-lg border border-coffee-200 focus:outline-none focus:ring-2 focus:ring-coffee-500 focus:border-coffee-500">
            </div>
        </div>

        <button type="submit" class="w-full px-4 py-3 text-sm font-semibold text-white bg-coffee-700 hover:bg-coffee-800 rounded-lg transition shadow-sm shadow-coffee-700/20">
            Criar minha conta
        </button>
    </form>
</div>
@endsection
