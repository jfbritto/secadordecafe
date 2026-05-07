@extends('layouts.guest')

@section('title', 'Criar conta')

@section('content')
<div class="auth-card">
    <h1>Criar fazenda</h1>
    <p class="muted">Comece com 14 dias de teste grátis.</p>

    <form method="POST" action="{{ route('register') }}" novalidate>
        @csrf

        <div class="field">
            <label for="farm_name">Nome da fazenda</label>
            <input id="farm_name" name="farm_name" type="text" value="{{ old('farm_name') }}" required autofocus>
            @error('farm_name')<small class="error">{{ $message }}</small>@enderror
        </div>

        <div class="field">
            <label for="name">Seu nome</label>
            <input id="name" name="name" type="text" value="{{ old('name') }}" required>
            @error('name')<small class="error">{{ $message }}</small>@enderror
        </div>

        <div class="field">
            <label for="email">E-mail</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required>
            @error('email')<small class="error">{{ $message }}</small>@enderror
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

        <button type="submit" class="btn btn-primary">Criar conta</button>
    </form>

    <p class="auth-footer">Já tem conta? <a href="{{ route('login') }}">Entrar</a></p>
</div>
@endsection
