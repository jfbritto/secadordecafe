@extends('layouts.guest')

@section('title', 'Entrar')

@section('content')
<div>
    <h1 class="text-3xl font-bold text-leaf-900">Entrar na sua conta</h1>
    <p class="text-sm text-leaf-500 mt-2 mb-8">Bem-vindo de volta! Acesse sua conta abaixo.</p>

    <form method="POST" action="{{ route('login') }}" class="space-y-5" novalidate>
        @csrf

        <div>
            <label for="email" class="block text-sm font-semibold text-leaf-900 mb-2">E-mail</label>
            <div class="relative">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-leaf-400 pointer-events-none">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                </span>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                       placeholder="seu@email.com"
                       class="w-full pl-12 pr-4 py-3.5 text-base rounded-lg border border-leaf-200 placeholder-leaf-300 focus:border-leaf-600 focus:ring-4 focus:ring-leaf-600/15 outline-none transition">
            </div>
            @error('email')<p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="password" class="block text-sm font-semibold text-leaf-900 mb-2">Senha</label>
            <div class="relative">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-leaf-400 pointer-events-none">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                </span>
                <input id="password" name="password" type="password" required
                       placeholder="sua senha"
                       class="w-full pl-12 pr-4 py-3.5 text-base rounded-lg border border-leaf-200 placeholder-leaf-300 focus:border-leaf-600 focus:ring-4 focus:ring-leaf-600/15 outline-none transition">
            </div>
            @error('password')<p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>@enderror
        </div>

        <div class="flex items-center justify-between">
            <label class="flex items-center gap-2 text-sm text-leaf-600 cursor-pointer">
                <input type="checkbox" name="remember" value="1" class="w-4 h-4 rounded border-leaf-300 text-leaf-700 focus:ring-leaf-500">
                Lembrar de mim
            </label>
        </div>

        <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-4 py-3.5 text-base font-bold text-white bg-leaf-700 hover:bg-leaf-800 rounded-lg transition shadow-md shadow-leaf-700/20">
            Entrar
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
        </button>
    </form>

    <div class="relative my-6">
        <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-leaf-100"></div></div>
        <div class="relative flex justify-center"><span class="px-3 bg-white text-xs text-leaf-400 uppercase tracking-wider">ou</span></div>
    </div>

    <p class="text-center text-sm text-leaf-600">
        Não tem conta? <a href="{{ route('register') }}" class="text-leaf-700 font-bold hover:underline">Criar minha roça gratuita</a>
    </p>
</div>
@endsection
