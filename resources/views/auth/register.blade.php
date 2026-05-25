@extends('layouts.guest')

@section('title', 'Criar conta')

@section('content')
<div>
    <h1 class="text-3xl font-bold text-leaf-900">Crie sua roça</h1>
    <p class="text-sm text-leaf-500 mt-2 mb-8">Acesso completo, grátis. Sem cartão de crédito.</p>

    <form method="POST" action="{{ route('register') }}" class="space-y-5" novalidate>
        @csrf

        <div>
            <label for="farm_name" class="block text-sm font-semibold text-leaf-900 mb-2">
                Nome da Fazenda
            </label>
            <div class="relative">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-leaf-400 pointer-events-none">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M5 21V7l7-4 7 4v14M9 9h.01M9 13h.01M9 17h.01M15 9h.01M15 13h.01M15 17h.01"/></svg>
                </span>
                <input id="farm_name" name="farm_name" type="text" value="{{ old('farm_name') }}" required autofocus maxlength="150"
                       placeholder="Ex: Fazenda Paraíso"
                       class="w-full pl-12 pr-4 py-3.5 text-base rounded-lg border border-leaf-200 placeholder-leaf-300 focus:border-leaf-600 focus:ring-4 focus:ring-leaf-600/15 outline-none transition">
            </div>
            @error('farm_name')<p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="name" class="block text-sm font-semibold text-leaf-900 mb-2">
                Seu Nome
            </label>
            <div class="relative">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-leaf-400 pointer-events-none">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </span>
                <input id="name" name="name" type="text" value="{{ old('name') }}" required maxlength="120"
                       placeholder="Seu nome completo"
                       class="w-full pl-12 pr-4 py-3.5 text-base rounded-lg border border-leaf-200 placeholder-leaf-300 focus:border-leaf-600 focus:ring-4 focus:ring-leaf-600/15 outline-none transition">
            </div>
            @error('name')<p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="email" class="block text-sm font-semibold text-leaf-900 mb-2">E-mail</label>
            <div class="relative">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-leaf-400 pointer-events-none">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                </span>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required
                       placeholder="seu@email.com"
                       class="w-full pl-12 pr-4 py-3.5 text-base rounded-lg border border-leaf-200 placeholder-leaf-300 focus:border-leaf-600 focus:ring-4 focus:ring-leaf-600/15 outline-none transition">
            </div>
            @error('email')<p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>@enderror
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="password" class="block text-sm font-semibold text-leaf-900 mb-2">Senha</label>
                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-leaf-400 pointer-events-none">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    </span>
                    <input id="password" name="password" type="password" required
                           placeholder="Mín. 8 caracteres"
                           class="w-full pl-12 pr-4 py-3.5 text-base rounded-lg border border-leaf-200 placeholder-leaf-300 focus:border-leaf-600 focus:ring-4 focus:ring-leaf-600/15 outline-none transition">
                </div>
                @error('password')<p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="password_confirmation" class="block text-sm font-semibold text-leaf-900 mb-2">Confirmar</label>
                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-leaf-400 pointer-events-none">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </span>
                    <input id="password_confirmation" name="password_confirmation" type="password" required
                           placeholder="Repita a senha"
                           class="w-full pl-12 pr-4 py-3.5 text-base rounded-lg border border-leaf-200 placeholder-leaf-300 focus:border-leaf-600 focus:ring-4 focus:ring-leaf-600/15 outline-none transition">
                </div>
            </div>
        </div>

        <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-4 py-3.5 text-base font-bold text-white bg-leaf-700 hover:bg-leaf-800 rounded-lg transition shadow-md shadow-leaf-700/20">
            Criar Conta Gratuita
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
        </button>

        {{-- Selos de confiança --}}
        <div class="flex flex-wrap items-center justify-center gap-5 text-xs text-leaf-500 pt-1">
            <span class="inline-flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                Dados seguros
            </span>
            <span class="inline-flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                Sem cobranças
            </span>
            <span class="inline-flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                Pronto em 1 min
            </span>
        </div>
    </form>

    <div class="relative my-6">
        <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-leaf-100"></div></div>
        <div class="relative flex justify-center"><span class="px-3 bg-white text-xs text-leaf-400 uppercase tracking-wider">ou</span></div>
    </div>

    <p class="text-center text-sm text-leaf-600">
        Já tem uma conta? <a href="{{ route('login') }}" class="text-leaf-700 font-bold hover:underline">Entrar</a>
    </p>
</div>
@endsection
