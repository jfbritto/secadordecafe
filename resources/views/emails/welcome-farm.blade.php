<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Bem-vindo</title>
</head>
<body style="font-family: system-ui, -apple-system, sans-serif; background:#f6f1ea; padding:32px;">
    <div style="max-width:520px; margin:0 auto; background:#fff; border-radius:12px; padding:32px;">
        <h1 style="color:#5a3a22; margin:0 0 12px;">Olá, {{ $userName }}!</h1>
        <p>Sua fazenda <strong>{{ $farmName }}</strong> foi criada com sucesso no <strong>secadordecafe</strong>.</p>
        <p>Você está com acesso completo durante o período de avaliação.</p>
        <p style="margin:24px 0;">
            <a href="{{ $dashboardUrl }}" style="background:#5a3a22; color:#fff; padding:11px 18px; border-radius:8px; text-decoration:none; font-weight:600;">Ir para o painel</a>
        </p>
        <p style="font-size:13px; color:#7d6b58;">Se você não criou esta conta, ignore este e-mail.</p>
    </div>
</body>
</html>
