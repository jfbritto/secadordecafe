@extends('layouts.app')

@section('title', 'Meu perfil')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-leaf-900">Meu perfil</h1>
        <p class="text-sm text-leaf-500 mt-1">Seus dados pessoais e senha de acesso à Roça Nossa.</p>
    </div>

    {{-- Card: dados pessoais --}}
    <form method="POST" action="{{ route('perfil.update') }}" class="bg-white rounded-2xl border border-leaf-100 shadow-sm p-6 sm:p-8 mb-6">
        @csrf
        @method('PUT')

        <div class="border-b border-leaf-100 pb-5 mb-6">
            <h2 class="text-base font-bold text-leaf-900">Dados pessoais</h2>
            <p class="text-sm text-leaf-500 mt-0.5">Como você aparece dentro do sistema.</p>
        </div>

        <div class="space-y-5">
            <div>
                <label for="name" class="block text-sm font-bold text-leaf-900 mb-2">
                    Nome <span class="text-rose-500">*</span>
                </label>
                <input id="name" type="text" name="name" required minlength="2" maxlength="120"
                       value="{{ old('name', $user->name) }}"
                       placeholder="ex: João da Silva"
                       class="w-full px-4 py-3 text-base rounded-lg border border-leaf-200 placeholder-leaf-300 focus:border-leaf-500 focus:ring-4 focus:ring-leaf-500/15 outline-none transition">
                @error('name')<p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="email" class="block text-sm font-bold text-leaf-900 mb-2">
                    E-mail <span class="text-rose-500">*</span>
                </label>
                <input id="email" type="email" name="email" required maxlength="180"
                       value="{{ old('email', $user->email) }}"
                       placeholder="seu@email.com"
                       class="w-full px-4 py-3 text-base rounded-lg border border-leaf-200 placeholder-leaf-300 focus:border-leaf-500 focus:ring-4 focus:ring-leaf-500/15 outline-none transition">
                <p class="mt-1.5 text-sm text-leaf-500">Usado para entrar no sistema e receber e-mails da plataforma.</p>
                @error('email')<p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="flex justify-end pt-6 mt-6 border-t border-leaf-100">
            <button type="submit" class="px-6 py-3 text-base font-bold text-white bg-leaf-700 hover:bg-leaf-800 rounded-lg transition shadow-sm">
                Salvar dados
            </button>
        </div>
    </form>

    {{-- Card: senha --}}
    <form method="POST" action="{{ route('perfil.senha.update') }}" class="bg-white rounded-2xl border border-leaf-100 shadow-sm p-6 sm:p-8">
        @csrf
        @method('PUT')

        <div class="border-b border-leaf-100 pb-5 mb-6">
            <h2 class="text-base font-bold text-leaf-900">Senha</h2>
            <p class="text-sm text-leaf-500 mt-0.5">Pra trocar a senha, informe a atual e a nova duas vezes.</p>
        </div>

        <div class="space-y-5">
            <div>
                <label for="current_password" class="block text-sm font-bold text-leaf-900 mb-2">
                    Senha atual <span class="text-rose-500">*</span>
                </label>
                <input id="current_password" type="password" name="current_password" required autocomplete="current-password"
                       placeholder="A senha que você usa pra entrar hoje"
                       class="w-full px-4 py-3 text-base rounded-lg border border-leaf-200 placeholder-leaf-300 focus:border-leaf-500 focus:ring-4 focus:ring-leaf-500/15 outline-none transition">
                @error('current_password')<p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div class="grid sm:grid-cols-2 gap-5">
                <div>
                    <label for="password" class="block text-sm font-bold text-leaf-900 mb-2">
                        Nova senha <span class="text-rose-500">*</span>
                    </label>
                    <input id="password" type="password" name="password" required autocomplete="new-password" minlength="8"
                           placeholder="Pelo menos 8 caracteres"
                           class="w-full px-4 py-3 text-base rounded-lg border border-leaf-200 placeholder-leaf-300 focus:border-leaf-500 focus:ring-4 focus:ring-leaf-500/15 outline-none transition">
                    <p class="mt-1.5 text-sm text-leaf-500">Mínimo 8 caracteres, com letras e números.</p>
                    @error('password')<p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="password_confirmation" class="block text-sm font-bold text-leaf-900 mb-2">
                        Repita a nova senha <span class="text-rose-500">*</span>
                    </label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" minlength="8"
                           placeholder="Repete a senha nova"
                           class="w-full px-4 py-3 text-base rounded-lg border border-leaf-200 placeholder-leaf-300 focus:border-leaf-500 focus:ring-4 focus:ring-leaf-500/15 outline-none transition">
                </div>
            </div>
        </div>

        <div class="flex justify-end pt-6 mt-6 border-t border-leaf-100">
            <button type="submit" class="px-6 py-3 text-base font-bold text-white bg-leaf-700 hover:bg-leaf-800 rounded-lg transition shadow-sm">
                Trocar senha
            </button>
        </div>
    </form>
</div>
@endsection
