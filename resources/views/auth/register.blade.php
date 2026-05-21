@extends('layouts.guest')

@section('title', 'Criar conta')

@section('content')
<div class="bg-white rounded-2xl shadow-xl shadow-leaf-700/5 border border-leaf-100 p-6 sm:p-8">
    <h1 class="text-2xl font-bold text-leaf-900">Criar fazenda</h1>
    <p class="text-sm text-leaf-500 mt-1 mb-6">Comece com 14 dias de teste grátis. Sem cartão de crédito.</p>

    <form method="POST" action="{{ route('register') }}" class="space-y-5" novalidate>
        @csrf

        <div>
            <label for="farm_name" class="block text-sm font-bold text-leaf-900 mb-2">
                Nome da fazenda <span class="text-rose-500">*</span>
            </label>
            <input id="farm_name" name="farm_name" type="text" value="{{ old('farm_name') }}" required autofocus maxlength="150"
                   placeholder="ex: Fazenda Paraíso"
                   class="w-full px-4 py-3 text-base rounded-lg border border-leaf-200 placeholder-leaf-300 focus:border-leaf-500 focus:ring-4 focus:ring-leaf-500/15 outline-none transition">
            <p class="mt-1.5 text-xs text-leaf-500">Como sua fazenda aparecerá no sistema.</p>
            @error('farm_name')<p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="name" class="block text-sm font-bold text-leaf-900 mb-2">
                Seu nome <span class="text-rose-500">*</span>
            </label>
            <input id="name" name="name" type="text" value="{{ old('name') }}" required maxlength="120"
                   placeholder="ex: João Silva"
                   class="w-full px-4 py-3 text-base rounded-lg border border-leaf-200 placeholder-leaf-300 focus:border-leaf-500 focus:ring-4 focus:ring-leaf-500/15 outline-none transition">
            @error('name')<p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="email" class="block text-sm font-bold text-leaf-900 mb-2">
                E-mail <span class="text-rose-500">*</span>
            </label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required
                   placeholder="seu@email.com"
                   class="w-full px-4 py-3 text-base rounded-lg border border-leaf-200 placeholder-leaf-300 focus:border-leaf-500 focus:ring-4 focus:ring-leaf-500/15 outline-none transition">
            <p class="mt-1.5 text-xs text-leaf-500">Você usa este e-mail para entrar e receber notificações.</p>
            @error('email')<p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>@enderror
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="password" class="block text-sm font-bold text-leaf-900 mb-2">
                    Senha <span class="text-rose-500">*</span>
                </label>
                <input id="password" name="password" type="password" required
                       placeholder="mínimo 8 caracteres"
                       class="w-full px-4 py-3 text-base rounded-lg border border-leaf-200 placeholder-leaf-300 focus:border-leaf-500 focus:ring-4 focus:ring-leaf-500/15 outline-none transition">
                <p class="mt-1.5 text-xs text-leaf-500">Letras + números, mínimo 8.</p>
                @error('password')<p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="password_confirmation" class="block text-sm font-bold text-leaf-900 mb-2">
                    Confirmar <span class="text-rose-500">*</span>
                </label>
                <input id="password_confirmation" name="password_confirmation" type="password" required
                       placeholder="repita a senha"
                       class="w-full px-4 py-3 text-base rounded-lg border border-leaf-200 placeholder-leaf-300 focus:border-leaf-500 focus:ring-4 focus:ring-leaf-500/15 outline-none transition">
            </div>
        </div>

        <button type="submit" class="w-full px-4 py-3.5 text-base font-bold text-white bg-leaf-700 hover:bg-leaf-800 rounded-lg transition shadow-sm shadow-leaf-700/20">
            Criar minha fazenda
        </button>
    </form>

    <p class="text-center text-sm text-leaf-500 mt-6">
        Já tem conta? <a href="{{ route('login') }}" class="text-leaf-700 font-bold hover:underline">Entrar</a>
    </p>
</div>
@endsection
