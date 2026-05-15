<!doctype html>
<html lang="pt-BR">
<head><meta charset="utf-8"><title>Bem-vindo à Roça Nossa</title></head>
<body style="font-family: system-ui, -apple-system, sans-serif; background:#faf6f1; padding:32px; margin:0;">
    <div style="max-width:560px; margin:0 auto; background:#fff; border-radius:16px; padding:36px; border:1px solid #f1e6d6;">
        <div style="display:flex; align-items:center; gap:10px; margin-bottom:20px;">
            <div style="width:36px; height:36px; border-radius:10px; background:#5a3a22; display:inline-flex; align-items:center; justify-content:center;">
                <span style="color:#fff; font-weight:700;">🌱</span>
            </div>
            <span style="font-size:18px; font-weight:700; color:#2b1c0e;">Roça Nossa</span>
        </div>
        <h1 style="color:#5a3a22; margin:0 0 8px; font-size:22px;">Bem-vindo, {{ $userName }}!</h1>
        <p style="color:#6e4322; margin:0 0 14px;">Sua roça <strong>{{ $farmName }}</strong> tá no sistema. Você tem 14 dias de uso completo pra colocar tudo no lugar — produtores, secagens, despesas e equipe.</p>
        <p style="color:#6e4322; margin:0 0 14px;">Quando estiver pronto, é só entrar e botar a roda pra girar:</p>
        <p style="margin:24px 0;">
            <a href="{{ $dashboardUrl }}" style="background:#5a3a22; color:#fff; padding:12px 22px; border-radius:8px; text-decoration:none; font-weight:600; display:inline-block;">Acessar a Roça Nossa</a>
        </p>
        <p style="font-size:13px; color:#a87a47; margin-top:24px;">Se não foi você que criou esta conta, pode ignorar este e-mail.</p>
    </div>
    <p style="text-align:center; color:#a87a47; font-size:12px; margin-top:18px;">© {{ date('Y') }} Roça Nossa — a tecnologia que entende a roça</p>
</body>
</html>
