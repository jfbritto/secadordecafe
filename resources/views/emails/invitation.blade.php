<!doctype html>
<html lang="pt-BR">
<head><meta charset="utf-8"><title>Convite — Roça Nossa</title></head>
<body style="font-family: system-ui, -apple-system, sans-serif; background:#faf6f1; padding:32px; margin:0;">
    <div style="max-width:560px; margin:0 auto; background:#fff; border-radius:16px; padding:36px; border:1px solid #f1e6d6;">
        <div style="display:flex; align-items:center; gap:10px; margin-bottom:20px;">
            <div style="width:36px; height:36px; border-radius:10px; background:#5a3a22; display:inline-flex; align-items:center; justify-content:center;">
                <span style="color:#fff; font-weight:700;">🌱</span>
            </div>
            <span style="font-size:18px; font-weight:700; color:#2b1c0e;">Roça Nossa</span>
        </div>
        <h1 style="color:#5a3a22; margin:0 0 8px; font-size:22px;">Você foi chamado pra roça</h1>
        <p style="color:#6e4322; margin:0 0 14px;">Te convidaram pra entrar na roça <strong>{{ $farmName }}</strong> como <strong>{{ ucfirst($role) }}</strong>.</p>
        <p style="color:#6e4322; margin:0 0 14px;">Clica no botão abaixo pra criar sua conta. Esse convite vale até <strong>{{ $expiresAt->format('d/m/Y H:i') }}</strong>.</p>
        <p style="margin:24px 0;">
            <a href="{{ $acceptUrl }}" style="background:#5a3a22; color:#fff; padding:12px 22px; border-radius:8px; text-decoration:none; font-weight:600; display:inline-block;">Aceitar convite</a>
        </p>
        <p style="font-size:12px; color:#a87a47; word-break:break-all;">Se o botão não funcionar, cole o link no navegador: {{ $acceptUrl }}</p>
    </div>
</body>
</html>
