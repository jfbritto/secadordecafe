@extends('layouts.guest')

@section('title', 'Entrar')

@section('content')
<div class="auth-card">
    <h1>Entrar</h1>

    <form method="POST" action="{{ route('login') }}" novalidate>
        @csrf

        <div class="field">
            <label for="email">E-mail</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus>
            @error('email')<small class="error">{{ $message }}</small>@enderror
        </div>

        <div class="field">
            <label for="password">Senha</label>
            <input id="password" name="password" type="password" required>
            @error('password')<small class="error">{{ $message }}</small>@enderror
        </div>

        <label class="inline">
            <input type="checkbox" name="remember" value="1"> Manter conectado
        </label>

        <button type="submit" class="btn btn-primary">Entrar</button>
    </form>

    <p class="auth-footer">Sem conta? <a href="{{ route('register') }}">Criar fazenda</a></p>
</div>
@endsection
