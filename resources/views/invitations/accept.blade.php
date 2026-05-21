@extends('layouts.guest')

@section('title', 'Aceitar convite')

@section('content')
<div class="bg-white rounded-2xl shadow-xl shadow-leaf-700/5 border border-leaf-100 p-6 sm:p-8">
    <h1 class="text-2xl font-bold text-leaf-900">Aceitar convite</h1>
    <p class="text-sm text-leaf-500 mt-1 mb-5">
        Você foi convidado para <strong class="text-leaf-800">{{ $invitation->farm?->nome }}</strong>
        como <strong class="text-leaf-800">{{ ucfirst($invitation->role) }}</strong>.
    </p>

    @if(session('error'))
        <div class="mb-4 px-4 py-3 rounded-lg bg-rose-50 border border-rose-200 text-rose-800 text-sm">{{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('convite.store', ['token' => $invitation->token]) }}" class="space-y-5" novalidate>
        @csrf

        <div>
            <label class="block text-sm font-bold text-leaf-900 mb-2">E-mail</label>
            <input type="email" value="{{ $invitation->email }}" disabled
                   class="w-full px-4 py-3 text-base rounded-lg border border-leaf-200 bg-leaf-50 text-leaf-600 cursor-not-allowed">
            <p class="mt-1.5 text-xs text-leaf-500">Não pode ser alterado. Use este e-mail para entrar.</p>
        </div>

        <div>
            <label for="name" class="block text-sm font-bold text-leaf-900 mb-2">
                Seu nome <span class="text-rose-500">*</span>
            </label>
            <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus maxlength="120"
                   placeholder="ex: Maria Souza"
                   class="w-full px-4 py-3 text-base rounded-lg border border-leaf-200 placeholder-leaf-300 focus:border-leaf-500 focus:ring-4 focus:ring-leaf-500/15 outline-none transition">
            @error('name')<p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>@enderror
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
            Criar minha conta
        </button>
    </form>
</div>
@endsection
