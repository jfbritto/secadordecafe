@extends('layouts.guest')

@section('title', 'Entrar')

@section('content')
<div class="bg-white rounded-2xl shadow-xl shadow-coffee-700/5 border border-coffee-100 p-6 sm:p-8">
    <h1 class="text-2xl font-bold text-coffee-900">Entrar</h1>
    <p class="text-sm text-coffee-500 mt-1 mb-6">Acesse sua conta para gerenciar a fazenda.</p>

    <form method="POST" action="{{ route('login') }}" class="space-y-5" novalidate>
        @csrf

        <div>
            <label for="email" class="block text-sm font-bold text-coffee-900 mb-2">E-mail</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                   placeholder="seu@email.com"
                   class="w-full px-4 py-3 text-base rounded-lg border border-coffee-200 placeholder-coffee-300 focus:border-coffee-500 focus:ring-4 focus:ring-coffee-500/15 outline-none transition">
            @error('email')<p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="password" class="block text-sm font-bold text-coffee-900 mb-2">Senha</label>
            <input id="password" name="password" type="password" required
                   placeholder="sua senha"
                   class="w-full px-4 py-3 text-base rounded-lg border border-coffee-200 placeholder-coffee-300 focus:border-coffee-500 focus:ring-4 focus:ring-coffee-500/15 outline-none transition">
            @error('password')<p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>@enderror
        </div>

        <label class="flex items-center gap-2 text-sm text-coffee-600">
            <input type="checkbox" name="remember" value="1" class="w-5 h-5 rounded border-coffee-300 text-coffee-700 focus:ring-coffee-500">
            Manter conectado neste dispositivo
        </label>

        <button type="submit" class="w-full px-4 py-3.5 text-base font-bold text-white bg-coffee-700 hover:bg-coffee-800 rounded-lg transition shadow-sm shadow-coffee-700/20">
            Entrar
        </button>
    </form>

    <p class="text-center text-sm text-coffee-500 mt-6">
        Sem conta? <a href="{{ route('register') }}" class="text-coffee-700 font-bold hover:underline">Criar fazenda</a>
    </p>
</div>
@endsection
