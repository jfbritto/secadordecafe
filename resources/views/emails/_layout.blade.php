<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>{{ $title ?? 'Roça Nossa' }}</title>
    <style>
        /* Reset mínimo. Maioria dos estilos é inline pra compatibilidade
           com clients de e-mail (Outlook, Gmail, Apple Mail, iOS, Android). */
        body { margin: 0; padding: 0; }
        a { color: #1e5631; }
        @media (prefers-color-scheme: dark) {
            body { background: #fdfbf4 !important; }
        }
    </style>
</head>
<body style="margin:0; padding:0; background:#fdfbf4; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color:#0a2415;">

    {{-- Wrapper externo (centraliza + dá respiro) --}}
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#fdfbf4;">
        <tr>
            <td align="center" style="padding:32px 16px;">

                {{-- Card branco do e-mail --}}
                <table role="presentation" width="560" cellpadding="0" cellspacing="0" border="0" style="max-width:560px; background:#ffffff; border-radius:16px; border:1px solid #d8c9a8; overflow:hidden;">

                    {{-- Header verde com logo + nome --}}
                    <tr>
                        <td style="background:#1e5631; padding:24px 36px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td width="48" style="vertical-align:middle; padding-right:14px;">
                                        <div style="width:48px; height:48px; border-radius:12px; background:rgba(255,255,255,0.15); text-align:center; line-height:48px;">
                                            <svg width="28" height="28" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg" style="vertical-align:middle;">
                                                <path d="M16 13v11" stroke="#fdfbf4" stroke-width="2" stroke-linecap="round" fill="none"/>
                                                <path d="M16 18c-4-.5-6-3-6-7 4 .5 6 3 6 7z" fill="#fdfbf4"/>
                                                <path d="M16 14c3-.5 5-2.5 5-6-3 .5-5 2.5-5 6z" fill="#8db580"/>
                                            </svg>
                                        </div>
                                    </td>
                                    <td style="vertical-align:middle;">
                                        <div style="font-size:20px; font-weight:700; color:#ffffff; line-height:1.2;">Roça Nossa</div>
                                        <div style="font-size:12px; color:#b3c49d; line-height:1.4; margin-top:2px;">A tecnologia que entende a roça</div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Corpo --}}
                    <tr>
                        <td style="padding:32px 36px 36px; color:#143a23; font-size:15px; line-height:1.55;">
                            {{ $slot ?? '' }}
                            @yield('content')
                        </td>
                    </tr>

                </table>

                {{-- Footer fora do card --}}
                <table role="presentation" width="560" cellpadding="0" cellspacing="0" border="0" style="max-width:560px; margin-top:18px;">
                    <tr>
                        <td align="center" style="color:#5da361; font-size:12px; line-height:1.5; padding:8px 0;">
                            © {{ date('Y') }} Roça Nossa, todos os direitos reservados.<br>
                            <a href="{{ url('/') }}" style="color:#5da361; text-decoration:none;">rocanossa.com.br</a>
                        </td>
                    </tr>
                </table>

            </td>
        </tr>
    </table>

</body>
</html>
