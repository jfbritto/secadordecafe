@extends('layouts.app')

@section('title', 'Usuários')

@section('content')
<h1 class="page">Usuários da fazenda</h1>

@if(session('flash'))
    <div class="card" style="margin-bottom:12px; background:#dcfce7;">{{ session('flash') }}</div>
@endif
@if(session('error'))
    <div class="card" style="margin-bottom:12px; background:#fee2e2; color:#991b1b;">{{ session('error') }}</div>
@endif

<div class="card" style="margin-bottom:16px;">
    <strong>Convidar novo usuário</strong>
    <form method="POST" action="{{ route('convites.store') }}" style="display:flex; gap:8px; margin-top:10px; flex-wrap:wrap;">
        @csrf
        <input type="email" name="email" placeholder="email@exemplo.com" required style="flex:1; min-width:240px;">
        <select name="role" style="padding:10px 12px; border:1px solid #d6c9b6; border-radius:8px;">
            <option value="operador">Operador</option>
            <option value="financeiro">Financeiro</option>
            <option value="visualizador">Visualizador</option>
            <option value="admin">Administrador</option>
        </select>
        <button type="submit" class="btn btn-primary" style="width:auto; padding:10px 16px;">Enviar convite</button>
    </form>
    @error('email')<small class="error" style="display:block; margin-top:6px;">{{ $message }}</small>@enderror
    @error('role')<small class="error" style="display:block; margin-top:6px;">{{ $message }}</small>@enderror
</div>

<div class="card" style="padding:0; overflow:hidden;">
    <table style="width:100%; border-collapse:collapse;">
        <thead style="background:#f9f4ec;">
            <tr>
                <th style="text-align:left; padding:10px 14px;">Nome</th>
                <th style="text-align:left; padding:10px 14px;">E-mail</th>
                <th style="text-align:left; padding:10px 14px;">Permissão</th>
                <th style="padding:10px 14px;"></th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $u)
                <tr style="border-top:1px solid #efe6d6;">
                    <td style="padding:10px 14px;">{{ $u->name }}@if($u->id === auth()->id()) <small style="color:#7d6b58;">(você)</small>@endif</td>
                    <td style="padding:10px 14px;">{{ $u->email }}</td>
                    <td style="padding:10px 14px;">
                        @if($u->id === auth()->id())
                            {{ $u->roles->pluck('name')->join(', ') ?: '—' }}
                        @else
                            <form method="POST" action="{{ route('usuarios.role.update', $u) }}" style="display:flex; gap:6px;">
                                @csrf @method('PUT')
                                <select name="role" onchange="this.form.submit()" style="padding:6px 8px; border:1px solid #d6c9b6; border-radius:6px;">
                                    @foreach(['admin','operador','financeiro','visualizador'] as $role)
                                        <option value="{{ $role }}" @selected($u->hasRole($role))>{{ ucfirst($role) }}</option>
                                    @endforeach
                                </select>
                            </form>
                        @endif
                    </td>
                    <td style="padding:10px 14px; text-align:right;">
                        @if($u->id !== auth()->id())
                            <form method="POST" action="{{ route('usuarios.destroy', $u) }}" onsubmit="return confirm('Remover este usuário?');" style="display:inline;">
                                @csrf @method('DELETE')
                                <button type="submit" style="background:transparent; border:0; color:#a23b3b; cursor:pointer;">remover</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div style="margin-top:14px;">{{ $users->links() }}</div>

@if($pendingInvitations->isNotEmpty())
    <h2 class="page" style="margin-top:30px; font-size:18px;">Convites pendentes</h2>
    <div class="card" style="padding:0; overflow:hidden;">
        <table style="width:100%; border-collapse:collapse;">
            <thead style="background:#f9f4ec;">
                <tr>
                    <th style="text-align:left; padding:10px 14px;">E-mail</th>
                    <th style="text-align:left; padding:10px 14px;">Permissão</th>
                    <th style="text-align:left; padding:10px 14px;">Expira em</th>
                    <th style="padding:10px 14px;"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($pendingInvitations as $inv)
                    <tr style="border-top:1px solid #efe6d6;">
                        <td style="padding:10px 14px;">{{ $inv->email }}</td>
                        <td style="padding:10px 14px;">{{ ucfirst($inv->role) }}</td>
                        <td style="padding:10px 14px;">{{ $inv->expires_at->format('d/m/Y H:i') }}</td>
                        <td style="padding:10px 14px; text-align:right;">
                            <form method="POST" action="{{ route('convites.destroy', $inv) }}" onsubmit="return confirm('Cancelar convite?');" style="display:inline;">
                                @csrf @method('DELETE')
                                <button type="submit" style="background:transparent; border:0; color:#a23b3b; cursor:pointer;">cancelar</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
@endsection
