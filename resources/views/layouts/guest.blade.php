<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>@yield('title', config('app.name'))</title>
    <style>
        :root { --pri:#5a3a22; --bg:#f6f1ea; --err:#a23b3b; --txt:#2b2218; }
        *,*::before,*::after { box-sizing:border-box; }
        body { font-family: system-ui, -apple-system, sans-serif; background:var(--bg); color:var(--txt); margin:0; min-height:100vh; display:flex; align-items:center; justify-content:center; padding:24px; }
        .auth-card { background:#fff; padding:32px; border-radius:12px; box-shadow:0 4px 24px rgba(0,0,0,.08); width:100%; max-width:420px; }
        h1 { margin:0 0 4px; color:var(--pri); }
        .muted { color:#7d6b58; margin-top:0; font-size:14px; }
        .field { margin-bottom:14px; }
        label { display:block; font-size:13px; margin-bottom:4px; font-weight:600; }
        input[type=text],input[type=email],input[type=password] { width:100%; padding:10px 12px; border:1px solid #d6c9b6; border-radius:8px; font-size:14px; background:#fff; }
        input:focus { outline:2px solid var(--pri); outline-offset:1px; }
        .inline { display:flex; align-items:center; gap:6px; font-size:13px; margin-bottom:16px; }
        .btn { display:inline-block; width:100%; padding:11px; border:0; border-radius:8px; font-weight:600; cursor:pointer; font-size:14px; }
        .btn-primary { background:var(--pri); color:#fff; }
        .btn-primary:hover { background:#3f2814; }
        small.error { color:var(--err); display:block; margin-top:4px; }
        .auth-footer { text-align:center; margin-top:18px; font-size:13px; color:#7d6b58; }
        .auth-footer a { color:var(--pri); }
    </style>
</head>
<body>
    @yield('content')
</body>
</html>
