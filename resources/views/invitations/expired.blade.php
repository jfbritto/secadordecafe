@extends('layouts.guest')

@section('title', 'Convite expirado')

@section('content')
<div class="auth-card">
    <h1>Convite expirado</h1>
    <p class="muted">Este convite expirou em {{ $invitation->expires_at->format('d/m/Y H:i') }}.</p>
    <p>Entre em contato com a administradora da fazenda <strong>{{ $invitation->farm?->nome }}</strong> para receber um novo.</p>
    <p class="auth-footer"><a href="{{ route('login') }}">Ir para login</a></p>
</div>
@endsection
