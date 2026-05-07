@extends('layouts.guest')

@section('title', 'Aceitar convite')

@section('content')
<div class="bg-white rounded-2xl shadow-xl shadow-coffee-700/5 border border-coffee-100 p-6 sm:p-8">
    <h1 class="text-2xl font-bold text-coffee-900">Aceitar convite</h1>
    <p class="text-sm text-coffee-500 mt-1 mb-5">
        Você foi convidado para <strong class="text-coffee-800">{{ $invitation->farm?->nome }}</strong>
        como <strong class="text-coffee-800">{{ ucfirst($invitation->role) }}</strong>.
    </p>

    @if(session('error'))
        <div class="mb-4 px-4 py-3 rounded-lg bg-rose-50 border border-rose-200 text-rose-800 text-sm">{{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('convite.store', ['token' => $invitation->token]) }}" class="space-y-5" novalidate>
        @csrf

        <div>
            <label class="block text-sm font-bold text-coffee-900 mb-2">E-mail</label>
            <input type="email" value="{{ $invitation->email }}" disabled
                   class="w-full px-4 py-3 text-base rounded-lg border border-coffee-200 bg-coffee-50 text-coffee-600 cursor-not-allowed">
            <p class="mt-1.5 text-xs text-coffee-500">Não pode ser alterado. Use este e-mail para entrar.</p>
        </div>

        <div>
            <label for="name" class="block text-sm font-bold text-coffee-900 mb-2">
                Seu nome <span class="text-rose-500">*</span>
            </label>
            <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus maxlength="120"
                   placeholder="ex: Maria Souza"
                   class="w-full px-4 py-3 text-base rounded-lg border border-coffee-200 placeholder-coffee-300 focus:border-coffee-500 focus:ring-4 focus:ring-coffee-500/15 outline-none transition">
            @error('name')<p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>@enderror
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="password" class="block text-sm font-bold text-coffee-900 mb-2">
                    Senha <span class="text-rose-500">*</span>
                </label>
                <input id="password" name="password" type="password" required
                       placeholder="mínimo 8 caracteres"
                       class="w-full px-4 py-3 text-base rounded-lg border border-coffee-200 placeholder-coffee-300 focus:border-coffee-500 focus:ring-4 focus:ring-coffee-500/15 outline-none transition">
                <p class="mt-1.5 text-xs text-coffee-500">Letras + números, mínimo 8.</p>
                @error('password')<p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="password_confirmation" class="block text-sm font-bold text-coffee-900 mb-2">
                    Confirmar <span class="text-rose-500">*</span>
                </label>
                <input id="password_confirmation" name="password_confirmation" type="password" required
                       placeholder="repita a senha"
                       class="w-full px-4 py-3 text-base rounded-lg border border-coffee-200 placeholder-coffee-300 focus:border-coffee-500 focus:ring-4 focus:ring-coffee-500/15 outline-none transition">
            </div>
        </div>

        <button type="submit" class="w-full px-4 py-3.5 text-base font-bold text-white bg-coffee-700 hover:bg-coffee-800 rounded-lg transition shadow-sm shadow-coffee-700/20">
            Criar minha conta
        </button>
    </form>
</div>
@endsection
