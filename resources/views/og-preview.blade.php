<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>OG Image Preview · Roça Nossa</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,600,700,900&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body {
            background: #2a2a2a;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            padding: 20px;
        }
        .og {
            width: 1200px;
            height: 630px;
            background: linear-gradient(135deg, #1e5631 0%, #0a2415 100%);
            position: relative;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,0.5);
        }
        .og::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at 30% 35%, rgba(45,138,74,0.4) 0%, transparent 55%);
        }
        .ground {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 35px;
            background: rgba(10, 36, 21, 0.6);
        }
        .deco-tl {
            position: absolute;
            top: 80px;
            right: 80px;
            width: 200px;
            height: 200px;
            opacity: 0.08;
        }
        .deco-bl {
            position: absolute;
            bottom: 100px;
            left: 60px;
            width: 220px;
            height: 180px;
            opacity: 0.06;
            transform: rotate(-15deg);
        }
        .content {
            position: relative;
            z-index: 2;
            display: flex;
            align-items: center;
            padding: 0 90px;
            height: 100%;
            gap: 60px;
        }
        .logo {
            width: 200px;
            height: 200px;
            border-radius: 50%;
            background: rgba(253, 251, 244, 0.08);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 0 0 20px rgba(253, 251, 244, 0.04);
        }
        .logo svg {
            width: 144px;
            height: 144px;
        }
        .text h1 {
            color: #fdfbf4;
            font-size: 100px;
            font-weight: 900;
            letter-spacing: -3px;
            line-height: 1;
            margin-bottom: 16px;
        }
        .text .subtitle {
            color: #b3c49d;
            font-size: 36px;
            font-weight: 400;
            letter-spacing: -0.5px;
        }
        .footer {
            position: absolute;
            bottom: 60px;
            left: 90px;
            right: 90px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            z-index: 2;
        }
        .tagline .main {
            color: rgba(253, 251, 244, 0.9);
            font-size: 22px;
            font-weight: 600;
        }
        .tagline .sub {
            color: rgba(141, 181, 128, 0.8);
            font-size: 16px;
            font-weight: 400;
            margin-top: 6px;
        }
        .url {
            color: rgba(253, 251, 244, 0.9);
            font-size: 20px;
            font-weight: 700;
        }
        .instructions {
            position: fixed;
            top: 20px;
            left: 20px;
            background: rgba(0,0,0,0.8);
            color: #fdfbf4;
            padding: 16px 20px;
            border-radius: 12px;
            font-size: 13px;
            max-width: 380px;
            line-height: 1.5;
        }
        .instructions strong { color: #8db580; }
        .instructions code { background: #1e5631; padding: 2px 6px; border-radius: 4px; font-family: monospace; font-size: 12px; }
        @media print {
            .instructions { display: none; }
        }
    </style>
</head>
<body>

<div class="instructions">
    <strong>📸 Como gerar o PNG:</strong><br>
    1. Abre o DevTools (<code>F12</code>)<br>
    2. <code>Cmd+Shift+P</code> (Mac) ou <code>Ctrl+Shift+P</code> (Win)<br>
    3. Digita <code>Capture node screenshot</code><br>
    4. Antes, no console clica no elemento <code>.og</code><br>
    5. Salva como <code>public/og-image.png</code>
</div>

<div class="og">
    <!-- Folhas decorativas -->
    <svg class="deco-tl" viewBox="0 0 200 200">
        <path d="M0 80c-30-15-40-50-30-90 30 15 40 50 30 90z" fill="#fdfbf4"/>
        <path d="M40 60c25-20 65-25 105 0-25 20-65 25-105 0z" fill="#fdfbf4"/>
        <path d="M-40 130c-15-30 5-65 40-80 15 30-5 65-40 80z" fill="#fdfbf4"/>
    </svg>
    <svg class="deco-bl" viewBox="0 0 220 180">
        <path d="M0 0c40 10 70 50 70 100-50-10-80-50-70-100z" fill="#fdfbf4"/>
        <path d="M80 40c30-30 80-30 110 0-30 30-80 30-110 0z" fill="#fdfbf4"/>
    </svg>

    <div class="ground"></div>

    <div class="content">
        <div class="logo">
            <svg viewBox="0 0 32 32" stroke-linecap="round" stroke-linejoin="round">
                <path d="M16 13v11" stroke="#fdfbf4" stroke-width="2" fill="none"/>
                <path d="M16 18c-4-.5-6-3-6-7 4 .5 6 3 6 7z" fill="#fdfbf4"/>
                <path d="M16 14c3-.5 5-2.5 5-6-3 .5-5 2.5-5 6z" fill="#8db580"/>
            </svg>
        </div>
        <div class="text">
            <h1>Roça Nossa</h1>
            <p class="subtitle">A tecnologia que entende a roça</p>
        </div>
    </div>

    <div class="footer">
        <div class="tagline">
            <div class="main">Acompanhe sua roça do plantio ao pagamento</div>
            <div class="sub">Produtores · Saldo · Secagens · Despesas · Equipe</div>
        </div>
        <div class="url">rocanossa.com.br</div>
    </div>
</div>

</body>
</html>
