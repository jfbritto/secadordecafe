@extends('layouts.app')

@section('title', 'Usuários')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-coffee-900">Usuários da fazenda</h1>
    <p class="text-sm text-coffee-500 mt-0.5">Gerencie quem tem acesso e suas permissões.</p>
</div>

<div class="bg-white rounded-2xl border border-coffee-100 shadow-sm p-6 mb-6">
    <div class="border-b border-coffee-100 pb-4 mb-5">
        <h2 class="text-base font-bold text-coffee-900">Convidar novo usuário</h2>
        <p class="text-sm text-coffee-500 mt-0.5">A pessoa recebe um e-mail com link para criar a conta. Convite válido por 7 dias.</p>
    </div>
    <form method="POST" action="{{ route('convites.store') }}">
        @csrf
        <div class="grid sm:grid-cols-12 gap-4">
            <div class="sm:col-span-6">
                <label for="invite_email" class="block text-sm font-bold text-coffee-900 mb-2">E-mail</label>
                <input id="invite_email" type="email" name="email" placeholder="exemplo@email.com" required
                       class="w-full px-4 py-3 text-base rounded-lg border border-coffee-200 placeholder-coffee-300 focus:border-coffee-500 focus:ring-4 focus:ring-coffee-500/15 outline-none transition">
                @error('email')<p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>@enderror
            </div>
            <div class="sm:col-span-4">
                <label for="invite_role" class="block text-sm font-bold text-coffee-900 mb-2">Permissão</label>
                <select id="invite_role" name="role"
                        class="w-full px-4 py-3 text-base rounded-lg border border-coffee-200 focus:border-coffee-500 focus:ring-4 focus:ring-coffee-500/15 outline-none transition bg-white">
                    <option value="operador">Operador (registra clientes e secagens)</option>
                    <option value="financeiro">Financeiro (cadastra despesas)</option>
                    <option value="visualizador">Visualizador (só consulta)</option>
                    <option value="admin">Administrador (acesso total)</option>
                </select>
                @error('role')<p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>@enderror
            </div>
            <div class="sm:col-span-2 flex items-end">
                <button type="submit" class="w-full px-4 py-3 text-base font-bold text-white bg-coffee-700 hover:bg-coffee-800 rounded-lg transition shadow-sm">Enviar</button>
            </div>
        </div>
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
