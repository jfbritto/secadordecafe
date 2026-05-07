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
                            <form method="POST" action="{{ route('usuarios.role.update', $u) }}" class="inline"
                                  data-confirm="Alterar permissão de {{ $u->name }}?"
                                  data-confirm-text="A nova permissão entra em vigor imediatamente. Confira a opção selecionada antes de confirmar."
                                  data-confirm-icon="question"
                                  data-confirm-yes="Sim, alterar"
                                  data-confirm-danger="0">
                                @csrf @method('PUT')
                                <select name="role" data-original="{{ $u->roles->pluck('name')->first() }}"
                                        onchange="if(this.value !== this.dataset.original) this.form.requestSubmit(); else this.form.dataset.confirmed='1';"
                                        class="px-2 py-1 text-sm rounded-md border border-coffee-200 focus:outline-none focus:ring-2 focus:ring-coffee-500">
                                    @foreach(['admin','operador','financeiro','visualizador'] as $role)
                                        <option value="{{ $role }}" @selected($u->hasRole($role))>{{ ucfirst($role) }}</option>
                                    @endforeach
                                </select>
                            </form>
                        @endif
                    </td>
                    <td class="px-6 py-3 text-right">
                        @if($u->id !== auth()->id())
                            <form method="POST" action="{{ route('usuarios.destroy', $u) }}"
                                  data-confirm="Remover {{ $u->name }} da fazenda?"
                                  data-confirm-text="A pessoa perde acesso imediatamente. Para dar acesso de novo, será preciso enviar um novo convite."
                                  data-confirm-yes="Sim, remover"
                                  class="inline">
                                @csrf @method('DELETE')
                                <button class="inline-flex items-center gap-1 text-rose-600 text-sm font-semibold hover:underline">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    remover
                                </button>
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
                            <form method="POST" action="{{ route('convites.destroy', $inv) }}"
                                  data-confirm="Cancelar convite para {{ $inv->email }}?"
                                  data-confirm-text="O link enviado por e-mail deixa de funcionar. Você pode enviar um novo convite a qualquer momento."
                                  data-confirm-yes="Sim, cancelar"
                                  class="inline">
                                @csrf @method('DELETE')
                                <button class="inline-flex items-center gap-1 text-rose-600 text-sm font-semibold hover:underline">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                    cancelar
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
@endsection
