<!doctype html>
<html lang="pt-BR">
<head><meta charset="utf-8"><title>Convite</title></head>
<body style="font-family: system-ui,-apple-system,sans-serif; background:#f6f1ea; padding:32px;">
    <div style="max-width:520px; margin:0 auto; background:#fff; border-radius:12px; padding:32px;">
        <h1 style="color:#5a3a22; margin:0 0 12px;">Você foi convidado</h1>
        <p>Você foi convidado para acessar a fazenda <strong>{{ $farmName }}</strong> no <strong>secadordecafe</strong> com a permissão de <strong>{{ ucfirst($role) }}</strong>.</p>
        <p>Clique no botão abaixo para criar sua conta. Este convite expira em <strong>{{ $expiresAt->format('d/m/Y H:i') }}</strong>.</p>
        <p style="margin:24px 0;">
            <a href="{{ $acceptUrl }}" style="background:#5a3a22; color:#fff; padding:11px 18px; border-radius:8px; text-decoration:none; font-weight:600;">Aceitar convite</a>
        </p>
        <p style="font-size:12px; color:#7d6b58; word-break:break-all;">Ou cole no navegador: {{ $acceptUrl }}</p>
    </div>
</body>
</html>
