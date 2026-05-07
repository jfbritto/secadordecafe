@extends('layouts.app')

@section('title', 'Usuários')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-coffee-900">Usuários da fazenda</h1>
    <p class="text-sm text-coffee-500 mt-0.5">Gerencie quem tem acesso e suas permissões.</p>
</div>

<div class="bg-white rounded-xl border border-coffee-100 shadow-sm p-6 mb-6">
    <h2 class="text-sm font-bold text-coffee-900 uppercase tracking-wider mb-4">Convidar novo usuário</h2>
    <form method="POST" action="{{ route('convites.store') }}">
        @csrf
        <div class="grid sm:grid-cols-3 gap-3">
            <input type="email" name="email" placeholder="email@exemplo.com" required
                   class="sm:col-span-2 px-3.5 py-2 text-sm rounded-lg border border-coffee-200 focus:outline-none focus:ring-2 focus:ring-coffee-500">
            <div class="flex gap-2">
                <select name="role" class="flex-1 px-3 py-2 text-sm rounded-lg border border-coffee-200 focus:outline-none focus:ring-2 focus:ring-coffee-500">
                    <option value="operador">Operador</option>
                    <option value="financeiro">Financeiro</option>
                    <option value="visualizador">Visualizador</option>
                    <option value="admin">Administrador</option>
                </select>
                <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-coffee-700 hover:bg-coffee-800 rounded-lg transition">Enviar</button>
            </div>
        </div>
        @error('email')<p class="mt-2 text-xs text-rose-600">{{ $message }}</p>@enderror
        @error('role')<p class="mt-2 text-xs text-rose-600">{{ $message }}</p>@enderror
    </form>
</div>

<div class="bg-white rounded-xl border border-coffee-100 shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-coffee-50/50 text-coffee-600 text-xs uppercase tracking-wider">
            <tr>
                <th class="text-left px-6 py-3 font-semibold">Nome</th>
                <th class="text-left px-6 py-3 font-semibold">E-mail</th>
                <th class="text-left px-6 py-3 font-semibold">Permissão</th>
                <th class="px-6 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-coffee-100">
            @foreach($users as $u)
                <tr class="hover:bg-coffee-50/30 transition">
                    <td class="px-6 py-3 text-coffee-900 font-medium">
                        {{ $u->name }}
                        @if($u->id === auth()->id())<span class="ml-1 text-xs text-coffee-500">(você)</span>@endif
                    </td>
                    <td class="px-6 py-3 text-coffee-600">{{ $u->email }}</td>
                    <td class="px-6 py-3">
                        @if($u->id === auth()->id())
                            <span class="text-coffee-700">{{ ucfirst($u->roles->pluck('name')->join(', ') ?: '—') }}</span>
                        @else
                            <form method="POST" action="{{ route('usuarios.role.update', $u) }}" class="inline">
                                @csrf @method('PUT')
                                <select name="role" onchange="this.form.submit()" class="px-2 py-1 text-sm rounded-md border border-coffee-200 focus:outline-none focus:ring-2 focus:ring-coffee-500">
                                    @foreach(['admin','operador','financeiro','visualizador'] as $role)
                                        <option value="{{ $role }}" @selected($u->hasRole($role))>{{ ucfirst($role) }}</option>
                                    @endforeach
                                </select>
                            </form>
                        @endif
                    </td>
                    <td class="px-6 py-3 text-right">
                        @if($u->id !== auth()->id())
                            <form method="POST" action="{{ route('usuarios.destroy', $u) }}" onsubmit="return confirm('Remover este usuário?');" class="inline">
                                @csrf @method('DELETE')
                                <button class="text-rose-600 text-sm hover:underline">remover</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $users->links() }}</div>

@if($pendingInvitations->isNotEmpty())
    <h2 class="text-sm font-bold text-coffee-900 uppercase tracking-wider mt-8 mb-3">Convites pendentes</h2>
    <div class="bg-white rounded-xl border border-coffee-100 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-coffee-50/50 text-coffee-600 text-xs uppercase tracking-wider">
                <tr>
                    <th class="text-left px-6 py-3 font-semibold">E-mail</th>
                    <th class="text-left px-6 py-3 font-semibold">Permissão</th>
                    <th class="text-left px-6 py-3 font-semibold">Expira</th>
                    <th class="px-6 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-coffee-100">
                @foreach($pendingInvitations as $inv)
                    <tr>
                        <td class="px-6 py-3 text-coffee-900">{{ $inv->email }}</td>
                        <td class="px-6 py-3 text-coffee-600">{{ ucfirst($inv->role) }}</td>
                        <td class="px-6 py-3 text-coffee-500">{{ $inv->expires_at->format('d/m/Y H:i') }}</td>
                        <td class="px-6 py-3 text-right">
                            <form method="POST" action="{{ route('convites.destroy', $inv) }}" onsubmit="return confirm('Cancelar convite?');" class="inline">
                                @csrf @method('DELETE')
                                <button class="text-rose-600 text-sm hover:underline">cancelar</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
@endsection
