@extends('layouts.guest')

@section('title', 'Aceitar convite')

@section('content')
<div class="auth-card">
    <h1>Aceitar convite</h1>
    <p class="muted">Você foi convidado para <strong>{{ $invitation->farm?->nome }}</strong> como <strong>{{ ucfirst($invitation->role) }}</strong>.</p>

    @if(session('error'))
        <div style="background:#fee2e2; color:#991b1b; padding:10px; border-radius:8px; margin-bottom:12px;">{{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('convite.store', ['token' => $invitation->token]) }}" novalidate>
        @csrf

        <div class="field">
            <label>E-mail</label>
            <input type="email" value="{{ $invitation->email }}" disabled>
        </div>

        <div class="field">
            <label for="name">Seu nome</label>
            <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus>
            @error('name')<small class="error">{{ $message }}</small>@enderror
        </div>

        <div class="field">
            <label for="password">Senha</label>
            <input id="password" name="password" type="password" required>
            @error('password')<small class="error">{{ $message }}</small>@enderror
        </div>

        <div class="field">
            <label for="password_confirmation">Confirmar senha</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required>
        </div>

        <button type="submit" class="btn btn-primary">Criar minha conta</button>
    </form>
</div>
@endsection
