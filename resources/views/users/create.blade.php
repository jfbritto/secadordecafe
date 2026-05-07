@extends('layouts.app')

@section('title', 'Cadastrar usuário')

@section('content')
@php
    use App\Support\PermissionsMatrix;
    $matrices = collect(array_keys(PermissionsMatrix::ROLES))
        ->mapWithKeys(fn ($r) => [$r => PermissionsMatrix::matrixFor($r)])
        ->all();
@endphp

<div class="max-w-5xl mx-auto">
    <div class="mb-6">
        <p class="text-xs text-coffee-500 mb-1">
            <a href="{{ route('usuarios.index') }}" class="hover:underline">Usuários</a> · <span class="text-coffee-700">Cadastrar</span>
        </p>
        <h1 class="text-2xl font-bold text-coffee-900">Cadastrar novo usuário</h1>
        <p class="text-sm text-coffee-500 mt-1">A pessoa entra direto com o e-mail e senha que você definir agora. Sem precisar de convite por e-mail.</p>
    </div>

    <form method="POST" action="{{ route('usuarios.store') }}"
          x-data="{ role: @js(old('role', 'operador')) }"
          class="space-y-6">
        @csrf

        <div class="bg-white rounded-2xl border border-coffee-100 shadow-sm p-6 sm:p-8">
            <div class="border-b border-coffee-100 pb-5 mb-6">
                <h2 class="text-base font-bold text-coffee-900">Dados de acesso</h2>
                <p class="text-sm text-coffee-500 mt-0.5">Como a pessoa vai entrar no sistema.</p>
            </div>

            <div class="grid sm:grid-cols-2 gap-5 mb-5">
                <div>
                    <label for="name" class="block text-sm font-bold text-coffee-900 mb-2">Nome <span class="text-rose-500">*</span></label>
                    <input id="name" name="name" type="text" required maxlength="120" autofocus
                           value="{{ old('name') }}"
                           placeholder="ex: Maria Souza"
                           class="w-full px-4 py-3 text-base rounded-lg border border-coffee-200 placeholder-coffee-300 focus:border-coffee-500 focus:ring-4 focus:ring-coffee-500/15 outline-none transition">
                    @error('name')<p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-bold text-coffee-900 mb-2">E-mail <span class="text-rose-500">*</span></label>
                    <input id="email" name="email" type="email" required
                           value="{{ old('email') }}"
                           placeholder="exemplo@email.com"
                           class="w-full px-4 py-3 text-base rounded-lg border border-coffee-200 placeholder-coffee-300 focus:border-coffee-500 focus:ring-4 focus:ring-coffee-500/15 outline-none transition">
                    <p class="mt-1.5 text-sm text-coffee-500">Será usado para entrar no sistema.</p>
                    @error('email')<p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-5">
                <div>
                    <label for="password" class="block text-sm font-bold text-coffee-900 mb-2">Senha <span class="text-rose-500">*</span></label>
                    <input id="password" name="password" type="password" required
                           placeholder="mínimo 8 caracteres"
                           class="w-full px-4 py-3 text-base rounded-lg border border-coffee-200 placeholder-coffee-300 focus:border-coffee-500 focus:ring-4 focus:ring-coffee-500/15 outline-none transition">
                    <p class="mt-1.5 text-sm text-coffee-500">Letras + números, mínimo 8. Combine com a pessoa pra ela poder mudar depois.</p>
                    @error('password')<p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="password_confirmation" class="block text-sm font-bold text-coffee-900 mb-2">Confirmar senha <span class="text-rose-500">*</span></label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required
                           placeholder="repita a senha"
                           class="w-full px-4 py-3 text-base rounded-lg border border-coffee-200 placeholder-coffee-300 focus:border-coffee-500 focus:ring-4 focus:ring-coffee-500/15 outline-none transition">
                </div>
            </div>
        </div>

        {{-- Permissões --}}
        <div class="bg-white rounded-2xl border border-coffee-100 shadow-sm p-6 sm:p-8">
            <div class="border-b border-coffee-100 pb-5 mb-6">
                <h2 class="text-base font-bold text-coffee-900">Permissão</h2>
                <p class="text-sm text-coffee-500 mt-0.5">Escolha o papel — abaixo aparece exatamente o que esta pessoa poderá fazer.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
                @foreach(\App\Support\PermissionsMatrix::ROLES as $key => $r)
                    <label class="flex items-start gap-3 p-4 rounded-lg border-2 cursor-pointer transition"
                           :class="role === @js($key) ? 'border-coffee-700 bg-coffee-50' : 'border-coffee-200 hover:bg-coffee-50/50'">
                        <input type="radio" name="role" value="{{ $key }}" x-model="role"
                               class="mt-0.5 w-5 h-5 border-coffee-300 text-coffee-700 focus:ring-coffee-500">
                        <div>
                            <span class="block text-sm font-bold text-coffee-900">{{ $r['label'] }}</span>
                            <span class="block text-xs text-coffee-500 mt-0.5 leading-relaxed">{{ $r['desc'] }}</span>
                        </div>
                    </label>
                @endforeach
            </div>
            @error('role')<p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>@enderror

            {{-- Matriz dinâmica --}}
            <div class="mt-6">
                <h3 class="text-sm font-bold text-coffee-900 uppercase tracking-wider mb-3">
                    O que <span x-text="@js(array_combine(array_keys(PermissionsMatrix::ROLES), array_column(PermissionsMatrix::ROLES, 'label')))[role]"></span> pode fazer
                </h3>
                <div class="overflow-x-auto -mx-2">
                    <table class="w-full text-sm">
                        <thead class="bg-coffee-50/50 text-coffee-600 text-xs uppercase tracking-wider">
                            <tr>
                                <th class="text-left px-4 py-2 font-semibold">Módulo</th>
                                @foreach(\App\Support\PermissionsMatrix::ACTIONS as $action => $aLabel)
                                    <th class="text-center px-4 py-2 font-semibold">{{ $aLabel }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-coffee-100">
                            @foreach(\App\Support\PermissionsMatrix::MODULES as $module => $mLabel)
                                <tr>
                                    <td class="px-4 py-2 font-medium text-coffee-800">{{ $mLabel }}</td>
                                    @foreach(\App\Support\PermissionsMatrix::ACTIONS as $action => $aLabel)
                                        <td class="px-4 py-2 text-center">
                                            @php
                                                $checks = [];
                                                foreach (array_keys(\App\Support\PermissionsMatrix::ROLES) as $r) {
                                                    $checks[$r] = $matrices[$r][$module][$action];
                                                }
                                            @endphp
                                            @foreach($checks as $r => $can)
                                                <span x-show="role === @js($r)" x-cloak>
                                                    @if($can)
                                                        <svg class="inline w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                                    @else
                                                        <span class="text-coffee-300">—</span>
                                                    @endif
                                                </span>
                                            @endforeach
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="flex flex-col-reverse sm:flex-row sm:items-center gap-3">
            <button type="submit" class="px-6 py-3 text-base font-bold text-white bg-coffee-700 hover:bg-coffee-800 rounded-lg transition shadow-sm w-full sm:w-auto">
                Cadastrar usuário
            </button>
            <a href="{{ route('usuarios.index') }}" class="text-center sm:text-left px-4 py-3 sm:py-0 text-sm font-semibold text-coffee-600 hover:text-coffee-900">Cancelar</a>
        </div>
    </form>
</div>
@endsection
