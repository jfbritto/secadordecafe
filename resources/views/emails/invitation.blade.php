@extends('emails._layout', ['title' => 'Convite · Roça Nossa'])

@section('content')

<h1 style="color:#1e5631; margin:0 0 12px; font-size:24px; font-weight:700; line-height:1.3;">
    Você foi chamado pra roça
</h1>

<p style="color:#143a23; margin:0 0 14px; font-size:15px; line-height:1.55;">
    Te convidaram pra entrar na roça <strong>{{ $farmName }}</strong> como
    <strong>{{ \App\Support\StatusLabels::role($role) }}</strong>.
</p>

<p style="color:#143a23; margin:0 0 24px; font-size:15px; line-height:1.55;">
    Clica no botão abaixo pra criar sua conta. Esse convite vale até
    <strong>{{ $expiresAt->format('d/m/Y') }} às {{ $expiresAt->format('H:i') }}</strong>.
</p>

{{-- Botão CTA --}}
<table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin:24px 0;">
    <tr>
        <td style="background:#1e5631; border-radius:10px;">
            <a href="{{ $acceptUrl }}"
               style="display:inline-block; padding:14px 28px; font-size:15px; font-weight:700; color:#ffffff; text-decoration:none; border-radius:10px;">
                Aceitar convite
            </a>
        </td>
    </tr>
</table>

{{-- Box informativo --}}
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:24px 0; background:#fdfbf4; border-radius:12px;">
    <tr>
        <td style="padding:16px 20px; font-size:13px; color:#143a23; line-height:1.5;">
            <strong style="color:#1e5631;">O que você vai poder fazer:</strong> depende do papel que te deram.
            Operadores cuidam do dia a dia (produtores, secagens), financeiro mexe nas despesas, visualizadores
            só consultam. Tudo fica registrado.
        </td>
    </tr>
</table>

<p style="font-size:12px; color:#5da361; margin:24px 0 0; line-height:1.5; word-break:break-all;">
    Se o botão não funcionar, cole este link no navegador:<br>
    <a href="{{ $acceptUrl }}" style="color:#5da361;">{{ $acceptUrl }}</a>
</p>

@endsection
