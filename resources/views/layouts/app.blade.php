<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>@yield('title', config('app.name'))</title>
    <style>
        :root { --pri:#5a3a22; --bg:#f6f1ea; --txt:#2b2218; --muted:#7d6b58; }
        *,*::before,*::after { box-sizing:border-box; }
        body { font-family: system-ui,-apple-system,sans-serif; margin:0; background:var(--bg); color:var(--txt); }
        header { background:var(--pri); color:#fff; padding:12px 24px; display:flex; align-items:center; justify-content:space-between; }
        header .brand { font-weight:700; font-size:16px; letter-spacing:.5px; }
        header nav { display:flex; align-items:center; gap:16px; font-size:14px; }
        header nav a, header nav button { color:#fff; background:transparent; border:0; cursor:pointer; font:inherit; }
        main { max-width:1180px; margin:0 auto; padding:24px; }
        .card { background:#fff; border-radius:12px; padding:20px; box-shadow:0 1px 4px rgba(0,0,0,.04); }
        .grid { display:grid; gap:16px; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); }
        .metric h3 { margin:0; font-size:13px; color:var(--muted); font-weight:600; text-transform:uppercase; letter-spacing:.5px; }
        .metric .value { font-size:28px; font-weight:700; margin-top:6px; color:var(--pri); }
        .badge { display:inline-block; padding:3px 10px; border-radius:999px; font-size:12px; font-weight:600; }
        .badge-trial { background:#fef3c7; color:#92400e; }
        .badge-active { background:#dcfce7; color:#166534; }
        .badge-blocked { background:#fee2e2; color:#991b1b; }
        h1.page { margin:0 0 16px; color:var(--pri); }
    </style>
</head>
<body>
    <header>
        <div class="brand">{{ config('app.name') }}</div>
        @auth
        <nav>
            <a href="{{ route('dashboard') }}">Dashboard</a>
            <a href="{{ route('clientes.index') }}">Clientes</a>
            <a href="{{ route('secagens.index') }}">Secagens</a>
            @if(auth()->user()->hasAnyRole(['admin','financeiro','visualizador']) || auth()->user()->isRoot())
                <a href="{{ route('despesas.index') }}">Despesas</a>
            @endif
            @if(auth()->user()->hasRole('admin') || auth()->user()->isRoot())
                <a href="{{ route('usuarios.index') }}">Usuários</a>
                <a href="{{ route('fazenda.edit') }}">Fazenda</a>
            @endif
            <span style="opacity:.6;">|</span>
            <span>{{ auth()->user()->name }}@if(auth()->user()->farm) — {{ auth()->user()->farm->nome }}@endif</span>
            <form method="POST" action="{{ route('logout') }}" style="display:inline;">@csrf
                <button type="submit">Sair</button>
            </form>
        </nav>
        @endauth
    </header>
    <main>
        @yield('content')
    </main>
</body>
</html>
