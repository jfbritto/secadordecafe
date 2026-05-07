@extends('layouts.guest')

@section('title', 'Criar conta')

@section('content')
<div class="bg-white rounded-2xl shadow-xl shadow-coffee-700/5 border border-coffee-100 p-8">
    <h1 class="text-2xl font-bold text-coffee-900">Criar fazenda</h1>
    <p class="text-sm text-coffee-500 mt-1 mb-6">Comece com 14 dias de teste grátis. Sem cartão de crédito.</p>

    <form method="POST" action="{{ route('register') }}" class="space-y-4" novalidate>
        @csrf

        <div>
            <label for="farm_name" class="block text-sm font-semibold text-coffee-800 mb-1.5">Nome da fazenda</label>
            <input id="farm_name" name="farm_name" type="text" value="{{ old('farm_name') }}" required autofocus
                   class="w-full px-3.5 py-2.5 text-sm rounded-lg border border-coffee-200 focus:outline-none focus:ring-2 focus:ring-coffee-500 focus:border-coffee-500">
            @error('farm_name')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="name" class="block text-sm font-semibold text-coffee-800 mb-1.5">Seu nome</label>
            <input id="name" name="name" type="text" value="{{ old('name') }}" required
                   class="w-full px-3.5 py-2.5 text-sm rounded-lg border border-coffee-200 focus:outline-none focus:ring-2 focus:ring-coffee-500 focus:border-coffee-500">
            @error('name')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="email" class="block text-sm font-semibold text-coffee-800 mb-1.5">E-mail</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required
                   class="w-full px-3.5 py-2.5 text-sm rounded-lg border border-coffee-200 focus:outline-none focus:ring-2 focus:ring-coffee-500 focus:border-coffee-500">
            @error('email')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
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
            Criar conta
        </button>
    </form>

    <p class="text-center text-sm text-coffee-500 mt-6">
        Já tem conta? <a href="{{ route('login') }}" class="text-coffee-700 font-semibold hover:underline">Entrar</a>
    </p>
</div>
@endsection
