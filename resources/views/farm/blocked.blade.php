@extends('layouts.app')

@section('title', 'Acesso Bloqueado')

@section('content')
<div class="card" style="max-width:560px; margin:48px auto; text-align:center;">
    <h1 class="page" style="text-align:center;">Acesso bloqueado</h1>
    <p>A fazenda <strong>{{ $farm?->nome ?? '—' }}</strong> está com o acesso temporariamente bloqueado.</p>
    <p>Isso normalmente acontece por inadimplência ou bloqueio administrativo. Entre em contato com o suporte para regularizar.</p>
    <form method="POST" action="{{ route('logout') }}" style="margin-top:18px;">@csrf
        <button type="submit" class="btn btn-primary" style="max-width:160px;">Sair</button>
    </form>
</div>
@endsection
