@extends('layouts.guest')

@section('title', 'Entrar')

@section('content')
<div class="bg-white rounded-2xl shadow-xl shadow-coffee-700/5 border border-coffee-100 p-8">
    <h1 class="text-2xl font-bold text-coffee-900">Entrar</h1>
    <p class="text-sm text-coffee-500 mt-1 mb-6">Acesse sua conta para gerenciar a fazenda.</p>

    <form method="POST" action="{{ route('login') }}" class="space-y-4" novalidate>
        @csrf

        <div>
            <label for="email" class="block text-sm font-semibold text-coffee-800 mb-1.5">E-mail</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                   class="w-full px-3.5 py-2.5 text-sm rounded-lg border border-coffee-200 focus:outline-none focus:ring-2 focus:ring-coffee-500 focus:border-coffee-500">
            @error('email')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="password" class="block text-sm font-semibold text-coffee-800 mb-1.5">Senha</label>
            <input id="password" name="password" type="password" required
                   class="w-full px-3.5 py-2.5 text-sm rounded-lg border border-coffee-200 focus:outline-none focus:ring-2 focus:ring-coffee-500 focus:border-coffee-500">
            @error('password')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
        </div>

        <label class="flex items-center gap-2 text-sm text-coffee-600">
            <input type="checkbox" name="remember" value="1" class="rounded border-coffee-300 text-coffee-700 focus:ring-coffee-500">
            Manter conectado
        </label>

        <button type="submit" class="w-full px-4 py-3 text-sm font-semibold text-white bg-coffee-700 hover:bg-coffee-800 rounded-lg transition shadow-sm shadow-coffee-700/20">
            Entrar
        </button>
    </form>

    <p class="text-center text-sm text-coffee-500 mt-6">
        Sem conta? <a href="{{ route('register') }}" class="text-coffee-700 font-semibold hover:underline">Criar fazenda</a>
    </p>
</div>
@endsection
