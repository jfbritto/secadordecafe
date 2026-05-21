<!doctype html>
<html lang="pt-BR">
<head><meta charset="utf-8"><title>Convite — Roça Nossa</title></head>
<body style="font-family: system-ui, -apple-system, sans-serif; background:#fdfbf4; padding:32px; margin:0; color:#0a2415;">
    <div style="max-width:560px; margin:0 auto; background:#fff; border-radius:16px; overflow:hidden; border:1px solid #d8c9a8;">
        <!-- Header verde com logo grande -->
        <div style="background:#1e5631; padding:28px 36px; display:flex; align-items:center; gap:14px;">
            <div style="width:48px; height:48px; border-radius:12px; background:rgba(255,255,255,0.15); display:inline-flex; align-items:center; justify-content:center; flex-shrink:0;">
                <svg width="28" height="28" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg">
                    <path d="M16 13v11" stroke="#fdfbf4" stroke-width="2" stroke-linecap="round" fill="none"/>
                    <path d="M16 18c-4-.5-6-3-6-7 4 .5 6 3 6 7z" fill="#fdfbf4"/>
                    <path d="M16 14c3-.5 5-2.5 5-6-3 .5-5 2.5-5 6z" fill="#8db580"/>
                </svg>
            </div>
            <span style="font-size:20px; font-weight:700; color:#fff;">Roça Nossa</span>
        </div>

        <!-- Corpo -->
        <div style="padding:32px 36px 36px;">
            <h1 style="color:#1e5631; margin:0 0 12px; font-size:24px;">Você foi chamado pra roça 🌾</h1>
            <p style="color:#143a23; margin:0 0 14px; line-height:1.55;">
                Te convidaram pra entrar na roça <strong>{{ $farmName }}</strong> como <strong>{{ ucfirst($role) }}</strong>.
            </p>
            <p style="color:#143a23; margin:0 0 24px; line-height:1.55;">
                Clica no botão abaixo pra criar sua conta. Esse convite vale até <strong>{{ $expiresAt->format('d/m/Y H:i') }}</strong>.
            </p>
            <p style="margin:24px 0;">
                <a href="{{ $acceptUrl }}" style="background:#1e5631; color:#fff; padding:14px 26px; border-radius:10px; text-decoration:none; font-weight:600; display:inline-block; font-size:15px;">Aceitar convite →</a>
            </p>
            <p style="font-size:12px; color:#5da361; word-break:break-all; margin-top:24px;">Se o botão não funcionar, cole o link no navegador: {{ $acceptUrl }}</p>
        </div>
    </div>
</body>
</html>
