@extends('layouts.guest')

@section('title', 'Convite já aceito')

@section('content')
<div class="auth-card">
    <h1>Convite já aceito</h1>
    <p class="muted">Este convite já foi aceito em {{ $invitation->accepted_at->format('d/m/Y H:i') }}.</p>
    <p class="auth-footer"><a href="{{ route('login') }}">Entrar</a></p>
</div>
@endsection
