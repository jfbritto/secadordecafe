<!doctype html>
<html lang="pt-BR">
<head><meta charset="utf-8"><title>Bem-vindo</title></head>
<body style="font-family: system-ui, -apple-system, sans-serif; background:#faf6f1; padding:32px; margin:0;">
    <div style="max-width:560px; margin:0 auto; background:#fff; border-radius:16px; padding:36px; border:1px solid #f1e6d6;">
        <div style="display:flex; align-items:center; gap:10px; margin-bottom:20px;">
            <div style="width:36px; height:36px; border-radius:10px; background:#5a3a22; display:inline-flex; align-items:center; justify-content:center;">
                <span style="color:#fff; font-weight:700;">☕</span>
            </div>
            <span style="font-size:18px; font-weight:700; color:#2b1c0e;">secadordecafe</span>
        </div>
        <h1 style="color:#5a3a22; margin:0 0 8px; font-size:22px;">Olá, {{ $userName }}!</h1>
        <p style="color:#6e4322; margin:0 0 14px;">Sua fazenda <strong>{{ $farmName }}</strong> foi criada com sucesso. Você está com acesso completo durante o período de avaliação.</p>
        <p style="margin:24px 0;">
            <a href="{{ $dashboardUrl }}" style="background:#5a3a22; color:#fff; padding:12px 22px; border-radius:8px; text-decoration:none; font-weight:600; display:inline-block;">Acessar painel</a>
        </p>
        <p style="font-size:13px; color:#a87a47; margin-top:24px;">Se você não criou esta conta, ignore este e-mail.</p>
    </div>
    <p style="text-align:center; color:#a87a47; font-size:12px; margin-top:18px;">© {{ date('Y') }} secadordecafe</p>
</body>
</html>
