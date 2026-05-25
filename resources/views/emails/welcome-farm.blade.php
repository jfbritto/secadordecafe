@extends('emails._layout', ['title' => 'Bem-vindo à Roça Nossa'])

@section('content')

<h1 style="color:#1e5631; margin:0 0 12px; font-size:24px; font-weight:700; line-height:1.3;">
    Bem-vindo, {{ $userName }}!
</h1>

<p style="color:#143a23; margin:0 0 14px; font-size:15px; line-height:1.55;">
    Sua roça <strong>{{ $farmName }}</strong> tá no sistema, com acesso completo
    pra colocar tudo no lugar: produtores, secagens, despesas e equipe.
</p>

<p style="color:#143a23; margin:0 0 24px; font-size:15px; line-height:1.55;">
    Quando estiver pronto, é só entrar e botar a roda pra girar.
</p>

{{-- Botão CTA (table-based pra compatibilidade com Outlook) --}}
<table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin:24px 0;">
    <tr>
        <td style="background:#1e5631; border-radius:10px;">
            <a href="{{ $dashboardUrl }}"
               style="display:inline-block; padding:14px 28px; font-size:15px; font-weight:700; color:#ffffff; text-decoration:none; border-radius:10px;">
                Acessar a Roça Nossa
            </a>
        </td>
    </tr>
</table>

{{-- Lista do que ele pode fazer --}}
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:24px 0; background:#fdfbf4; border-radius:12px;">
    <tr>
        <td style="padding:18px 20px;">
            <p style="margin:0 0 10px; color:#1e5631; font-size:13px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px;">
                Por onde começar
            </p>
            <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                <tr><td style="padding:4px 0; font-size:14px; color:#143a23;">1. Cadastra seus produtores e o saldo de café de cada um</td></tr>
                <tr><td style="padding:4px 0; font-size:14px; color:#143a23;">2. Lança a primeira secagem com a turma toda</td></tr>
                <tr><td style="padding:4px 0; font-size:14px; color:#143a23;">3. Convida sua equipe pra ajudar</td></tr>
            </table>
        </td>
    </tr>
</table>

<p style="font-size:13px; color:#5da361; margin:32px 0 0; line-height:1.5;">
    Se não foi você quem se cadastrou, pode ignorar este e-mail.
</p>

@endsection
