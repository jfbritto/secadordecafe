<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Roça Nossa, a tecnologia que entende a roça</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.svg') }}">
    <link rel="canonical" href="{{ url('/') }}">
    <meta name="description" content="Roça Nossa é o sistema que acompanha o produtor do plantio ao pagamento. Cliente, saldo, secagem, despesas e equipe, tudo no mesmo lugar, na palma da mão. Teste grátis por 14 dias.">
    <meta name="keywords" content="roça nossa, gestão de fazenda, controle de produtor rural, software para roça, secagem de café, controle de lavoura, sistema para fazenda, gestão rural, agronegócio">
    <meta name="theme-color" content="#1e5631">

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Roça Nossa">
    <meta property="og:title" content="Roça Nossa, a tecnologia que entende a roça">
    <meta property="og:description" content="Acompanhe sua roça do plantio ao pagamento. Café, lavoura, despesas e equipe, tudo no mesmo lugar, na palma da mão.">
    <meta property="og:locale" content="pt_BR">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:image" content="{{ asset('og-image.png') }}">
    <meta property="og:image:secure_url" content="{{ asset('og-image.png') }}">
    <meta property="og:image:type" content="image/png">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="Roça Nossa, A tecnologia que entende a roça">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Roça Nossa, a tecnologia que entende a roça">
    <meta name="twitter:description" content="Acompanhe sua roça do plantio ao pagamento. Café, lavoura, despesas e equipe, tudo no mesmo lugar.">
    <meta name="twitter:image" content="{{ asset('og-image.png') }}">
    <meta name="twitter:image:alt" content="Roça Nossa, A tecnologia que entende a roça">

    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Roça Nossa",
        "applicationCategory": "BusinessApplication",
        "operatingSystem": "Web",
        "url": "{{ url('/') }}",
        "description": "Plataforma SaaS multi-tenant para gestão da roça, produtores, saldos, secagens, despesas e equipe.",
        "offers": {
            "@type": "Offer",
            "price": "99.90",
            "priceCurrency": "BRL",
            "description": "14 dias de teste grátis. Mensal."
        }
    }
    </script>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { theme: { extend: { colors: { leaf: {
            50:'#fdfbf4',100:'#f4ead4',200:'#d8c9a8',300:'#b3c49d',400:'#8db580',
            500:'#5da361',600:'#2d8a4a',700:'#1e5631',800:'#143a23',900:'#0a2415',
        } }, fontFamily: { sans: ['Inter','system-ui','sans-serif'] } } } }
    </script>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', system-ui, sans-serif; }
        @keyframes float { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-12px); } }
        .float-1 { animation: float 6s ease-in-out infinite; }
        .float-2 { animation: float 7s ease-in-out infinite reverse; }
    </style>
</head>
<body class="bg-white text-leaf-900">

<header class="sticky top-0 z-40 bg-white/85 backdrop-blur-lg border-b border-leaf-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <a href="/" class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-leaf-600 to-leaf-800 flex items-center justify-center shadow-md shadow-leaf-700/30">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M7 20h10"/>
                        <path d="M12 20V9"/>
                        <path d="M12 14c-3 0-5-2-5-5 3 0 5 2 5 5z"/>
                        <path d="M12 11c2.5 0 5-1.5 5-5-2.5 0-5 1.5-5 5z"/>
                    </svg>
                </div>
                <span class="text-lg font-bold text-leaf-900">Roça Nossa</span>
            </a>
            <nav class="hidden md:flex items-center gap-7 text-sm font-medium text-leaf-600">
                <a href="#features" class="hover:text-leaf-900 transition">O que faz</a>
                <a href="#planos" class="hover:text-leaf-900 transition">Planos</a>
                <a href="#como-funciona" class="hover:text-leaf-900 transition">Como funciona</a>
                <a href="#faq" class="hover:text-leaf-900 transition">Dúvidas</a>
            </nav>
            <div class="flex items-center gap-2">
                @auth
                    <a href="{{ route('dashboard') }}" class="px-4 py-2 text-sm font-semibold text-white bg-leaf-700 hover:bg-leaf-800 rounded-lg transition shadow-sm">Painel</a>
                @else
                    <a href="{{ route('login') }}" class="hidden sm:inline-block px-4 py-2 text-sm font-medium text-leaf-600 hover:text-leaf-900 transition">Entrar</a>
                    <a href="{{ route('register') }}" class="px-4 py-2 text-sm font-semibold text-white bg-leaf-700 hover:bg-leaf-800 rounded-lg transition shadow-sm shadow-leaf-700/20">Criar minha roça</a>
                @endauth
            </div>
        </div>
    </div>
</header>

<section class="relative overflow-hidden py-20 sm:py-28">
    <div class="absolute inset-0 bg-gradient-to-br from-leaf-50 via-white to-amber-50/40"></div>
    <div class="absolute top-20 right-10 w-72 h-72 bg-leaf-200 rounded-full opacity-30 blur-3xl float-1"></div>
    <div class="absolute bottom-10 left-10 w-96 h-96 bg-amber-200 rounded-full opacity-20 blur-3xl float-2"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1.5 text-xs font-semibold text-leaf-700 bg-leaf-100 rounded-full mb-6">
                    <span class="w-2 h-2 bg-leaf-600 rounded-full animate-pulse"></span>
                    Feito pra quem trabalha na roça
                </div>
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold leading-[1.05] tracking-tight mb-6">
                    A roça é nossa.
                    <span class="bg-gradient-to-r from-leaf-700 to-amber-700 bg-clip-text text-transparent">O controle também.</span>
                </h1>
                <p class="text-lg text-leaf-600 leading-relaxed mb-8 max-w-xl">
                    Acompanhe seus produtores do plantio ao pagamento. Saldo de café, secagens, despesas, equipe e
                    auditoria, tudo na palma da mão, em uma língua que o roceiro entende.
                </p>
                <div class="flex flex-col sm:flex-row gap-3">
                    <a href="{{ route('register') }}" class="inline-flex items-center justify-center gap-2 px-8 py-4 text-base font-bold text-white bg-gradient-to-r from-leaf-700 to-leaf-800 hover:from-leaf-800 hover:to-leaf-900 rounded-xl transition-all shadow-lg shadow-leaf-700/30 hover:-translate-y-0.5">
                        Experimente 14 dias grátis
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                    </a>
                    <a href="#planos" class="inline-flex items-center justify-center gap-2 px-6 py-4 text-sm font-semibold text-leaf-700 bg-white border border-leaf-200 hover:border-leaf-400 rounded-xl transition-all">
                        Ver planos
                    </a>
                </div>
                <p class="mt-4 text-xs text-leaf-500">Sem cartão de crédito no teste. Cancele quando quiser.</p>
            </div>

            <div class="hidden lg:block relative">
                <div class="relative bg-white rounded-2xl shadow-2xl shadow-leaf-700/20 border border-leaf-100 overflow-hidden">
                    <div class="bg-gradient-to-r from-leaf-700 to-leaf-800 px-6 py-3 flex items-center gap-2">
                        <div class="flex gap-1.5">
                            <div class="w-3 h-3 rounded-full bg-white/30"></div>
                            <div class="w-3 h-3 rounded-full bg-white/30"></div>
                            <div class="w-3 h-3 rounded-full bg-white/30"></div>
                        </div>
                        <span class="text-white/80 text-xs ml-3 font-mono">app.rocanossa.com.br/secagens/12</span>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs text-leaf-500 font-semibold uppercase tracking-wider">Secagem #12</p>
                                <p class="text-lg font-bold text-leaf-900">15/05/2026 · Secador 1</p>
                            </div>
                            <span class="px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-700 text-xs font-bold">CONCLUÍDA</span>
                        </div>
                        <div class="bg-leaf-50/50 rounded-xl overflow-hidden">
                            <table class="w-full text-xs">
                                <thead class="bg-leaf-100/60 text-leaf-700">
                                    <tr>
                                        <th class="text-left p-2 font-semibold">Produtor</th>
                                        <th class="text-right p-2 font-semibold">Recebido</th>
                                        <th class="text-right p-2 font-semibold">Seco</th>
                                        <th class="text-right p-2 font-semibold">Líquido</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="border-b border-leaf-100">
                                        <td class="p-2 text-leaf-900 font-medium">João Almeida</td>
                                        <td class="p-2 text-right text-leaf-700">600 kg</td>
                                        <td class="p-2 text-right text-leaf-700">360 kg</td>
                                        <td class="p-2 text-right font-bold text-leaf-800">342 kg</td>
                                    </tr>
                                    <tr class="border-b border-leaf-100">
                                        <td class="p-2 text-leaf-900 font-medium">Carlos Souza</td>
                                        <td class="p-2 text-right text-leaf-700">300 kg</td>
                                        <td class="p-2 text-right text-leaf-700">182 kg</td>
                                        <td class="p-2 text-right font-bold text-leaf-800">173 kg</td>
                                    </tr>
                                    <tr>
                                        <td class="p-2 text-leaf-900 font-medium">Pedro Lima</td>
                                        <td class="p-2 text-right text-leaf-700">200 kg</td>
                                        <td class="p-2 text-right text-leaf-700">125 kg</td>
                                        <td class="p-2 text-right font-bold text-leaf-800">119 kg</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="grid grid-cols-3 gap-2">
                            <div class="bg-leaf-50 rounded-lg p-2.5 text-center"><p class="text-base font-bold text-leaf-700">1.100</p><p class="text-[10px] text-leaf-500 uppercase tracking-wider">Recebido</p></div>
                            <div class="bg-emerald-50 rounded-lg p-2.5 text-center"><p class="text-base font-bold text-emerald-700">667</p><p class="text-[10px] text-emerald-500 uppercase tracking-wider">Seco</p></div>
                            <div class="bg-amber-50 rounded-lg p-2.5 text-center"><p class="text-base font-bold text-amber-700">60,6%</p><p class="text-[10px] text-amber-500 uppercase tracking-wider">Rendim.</p></div>
                        </div>
                    </div>
                </div>
                <div class="absolute -bottom-6 -left-6 w-24 h-24 bg-leaf-100 rounded-2xl rotate-12 opacity-60 -z-10"></div>
                <div class="absolute -top-4 -right-4 w-16 h-16 bg-amber-100 rounded-xl -rotate-12 opacity-60 -z-10"></div>
            </div>
        </div>
    </div>
</section>

<section id="features" class="py-24 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <p class="text-sm font-semibold text-leaf-700 uppercase tracking-wider mb-2">O que faz</p>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-leaf-900 mb-4">Tudo o que sua roça precisa</h2>
            <p class="text-lg text-leaf-500 max-w-2xl mx-auto">A Roça Nossa começou no café e cresce com você. Substitui caderno, planilha e WhatsApp num lugar só.</p>
        </div>

        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            @php
                $features = [
                    ['title'=>'Cadastro do produtor', 'desc'=>'Ficha completa de cada produtor: contato, CPF/CNPJ, observações e o saldo de café em estoque. Histórico do plantio ao pagamento.', 'icon'=>'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z', 'color'=>'coffee'],
                    ['title'=>'Secagem multi-produtor', 'desc'=>'Uma secagem com vários da turma. Recebido, seco, comissão e líquido por linha, totalizado automaticamente. Chega de pegar no lápis.', 'icon'=>'M15.362 5.214A8.252 8.252 0 0112 21 8.25 8.25 0 016.038 7.048 8.287 8.287 0 009 9.6a8.983 8.983 0 013.361-6.867 8.21 8.21 0 003 2.48z', 'color'=>'amber'],
                    ['title'=>'Extrato da roça', 'desc'=>'Cada produtor tem um extrato como conta de banco. Entrada, secagem, ajuste e saída, saldo após cada lançamento, com link da origem.', 'icon'=>'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', 'color'=>'emerald'],
                    ['title'=>'Cálculo automático', 'desc'=>'Rendimento (seco / recebido), comissão em kg e saldo líquido na hora. Se errar a conta, é a tecnologia, você confere e segue.', 'icon'=>'M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z', 'color'=>'sky'],
                    ['title'=>'Despesas da roça', 'desc'=>'Combustível, mão de obra, adubo, manutenção, categorize, filtre por mês e tenha o total na ponta da língua na hora de fechar o caixa.', 'icon'=>'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'color'=>'rose'],
                    ['title'=>'Equipe na medida', 'desc'=>'Cada um com sua chave. Administrador, operador, financeiro ou só pra ver, você decide quem pode o quê.', 'icon'=>'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z', 'color'=>'indigo'],
                    ['title'=>'Convite por e-mail', 'desc'=>'Chame o filho, a esposa, o gerente. Convite com prazo de validade e sem senha compartilhada, cada um faz a sua.', 'icon'=>'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z', 'color'=>'cyan'],
                    ['title'=>'Relatório em PDF', 'desc'=>'Imprima ou mande no WhatsApp do produtor, a Secagem #42 sai pronta pra entregar, com a sua marca.', 'icon'=>'M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3', 'color'=>'purple'],
                    ['title'=>'Tudo registrado', 'desc'=>'Quem mexeu, quando mexeu, o que mudou. Auditoria completa pra você dormir tranquilo sabendo o que aconteceu na roça.', 'icon'=>'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'color'=>'teal'],
                ];
                $cm = [
                    'coffee'=>['bg'=>'bg-leaf-100','text'=>'text-leaf-700','hover'=>'group-hover:bg-leaf-700','htext'=>'group-hover:text-white'],
                    'amber'=>['bg'=>'bg-amber-100','text'=>'text-amber-700','hover'=>'group-hover:bg-amber-600','htext'=>'group-hover:text-white'],
                    'emerald'=>['bg'=>'bg-emerald-100','text'=>'text-emerald-700','hover'=>'group-hover:bg-emerald-600','htext'=>'group-hover:text-white'],
                    'sky'=>['bg'=>'bg-sky-100','text'=>'text-sky-700','hover'=>'group-hover:bg-sky-600','htext'=>'group-hover:text-white'],
                    'rose'=>['bg'=>'bg-rose-100','text'=>'text-rose-700','hover'=>'group-hover:bg-rose-600','htext'=>'group-hover:text-white'],
                    'indigo'=>['bg'=>'bg-indigo-100','text'=>'text-indigo-700','hover'=>'group-hover:bg-indigo-600','htext'=>'group-hover:text-white'],
                    'cyan'=>['bg'=>'bg-cyan-100','text'=>'text-cyan-700','hover'=>'group-hover:bg-cyan-600','htext'=>'group-hover:text-white'],
                    'purple'=>['bg'=>'bg-purple-100','text'=>'text-purple-700','hover'=>'group-hover:bg-purple-600','htext'=>'group-hover:text-white'],
                    'teal'=>['bg'=>'bg-teal-100','text'=>'text-teal-700','hover'=>'group-hover:bg-teal-600','htext'=>'group-hover:text-white'],
                ];
            @endphp
            @foreach($features as $f)
                @php $c = $cm[$f['color']]; @endphp
                <div class="group p-7 rounded-2xl border border-leaf-100 hover:border-leaf-200 hover:shadow-lg transition-all">
                    <div class="w-11 h-11 rounded-xl {{ $c['bg'] }} {{ $c['hover'] }} flex items-center justify-center mb-4 transition-colors">
                        <svg class="w-5 h-5 {{ $c['text'] }} {{ $c['htext'] }} transition-colors" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $f['icon'] }}"/>
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-leaf-900 mb-1.5">{{ $f['title'] }}</h3>
                    <p class="text-sm text-leaf-500 leading-relaxed">{{ $f['desc'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section id="como-funciona" class="py-24 bg-leaf-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <p class="text-sm font-semibold text-leaf-700 uppercase tracking-wider mb-2">Como funciona</p>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-leaf-900 mb-4">Da inscrição à primeira secagem em 5 minutos</h2>
        </div>
        <div class="grid md:grid-cols-3 gap-6">
            @foreach([
                ['n'=>'01','t'=>'Crie sua roça no sistema','d'=>'Cadastre a fazenda em menos de 1 minuto e ganhe 14 dias pra usar à vontade. Sem cartão, sem compromisso.'],
                ['n'=>'02','t'=>'Anote os produtores','d'=>'Coloque cada produtor com o saldo de café que ele já tem com você. Convide o resto da turma pelo e-mail.'],
                ['n'=>'03','t'=>'Bote a roda pra girar','d'=>'Lance secagens com vários produtores juntos. O sistema cuida da conta e o extrato fica em dia sozinho.'],
            ] as $step)
                <div class="bg-white rounded-2xl border border-leaf-100 p-7 shadow-sm">
                    <div class="text-5xl font-extrabold bg-gradient-to-br from-leaf-500 to-leaf-700 bg-clip-text text-transparent mb-3">{{ $step['n'] }}</div>
                    <h3 class="text-lg font-bold text-leaf-900 mb-2">{{ $step['t'] }}</h3>
                    <p class="text-sm text-leaf-500">{{ $step['d'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section id="planos" class="py-24 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <p class="text-sm font-semibold text-leaf-700 uppercase tracking-wider mb-2">Planos</p>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-leaf-900 mb-4">Comece grátis. Pague conforme a roça cresce.</h2>
            <p class="text-lg text-leaf-500 max-w-2xl mx-auto">14 dias de teste em qualquer plano. Mensal, cancele quando quiser.</p>
        </div>

        <div class="grid md:grid-cols-3 gap-6 max-w-5xl mx-auto">
            @php
                $plans = [
                    ['name'=>'Experiência','price'=>'Grátis','period'=>'14 dias','cta'=>'Começar agora','featured'=>false,'features'=>['Acesso completo','1 roça','Sem cartão de crédito','Suporte por e-mail']],
                    ['name'=>'Roça Cheia','price'=>'R$ 99,90','period'=>'/mês','cta'=>'Começar agora','featured'=>true,'features'=>['1 roça','Equipe ilimitada','Produtores ilimitados','Secagens ilimitadas','PDF das secagens','Auditoria completa','Suporte prioritário']],
                    ['name'=>'Cooperativa','price'=>'Sob medida','period'=>'','cta'=>'Falar com a gente','featured'=>false,'features'=>['Várias roças no mesmo grupo','SLA dedicado','Importação de planilhas','Treinamento da equipe','Integrações sob medida']],
                ];
            @endphp
            @foreach($plans as $p)
                <div class="rounded-2xl border {{ $p['featured'] ? 'border-leaf-700 shadow-2xl shadow-leaf-700/15 ring-2 ring-leaf-700' : 'border-leaf-100 shadow-sm' }} bg-white p-7 relative">
                    @if($p['featured'])
                        <span class="absolute -top-3 left-1/2 -translate-x-1/2 px-3 py-1 rounded-full bg-leaf-700 text-white text-[10px] font-bold uppercase tracking-wider">Mais escolhido</span>
                    @endif
                    <h3 class="text-lg font-bold text-leaf-900">{{ $p['name'] }}</h3>
                    <div class="mt-4 mb-5">
                        <span class="text-4xl font-extrabold text-leaf-900">{{ $p['price'] }}</span>
                        <span class="text-sm text-leaf-500 ml-1">{{ $p['period'] }}</span>
                    </div>
                    <ul class="space-y-2.5 mb-7 text-sm text-leaf-700">
                        @foreach($p['features'] as $f)
                            <li class="flex items-start gap-2">
                                <svg class="w-4 h-4 text-emerald-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                {{ $f }}
                            </li>
                        @endforeach
                    </ul>
                    <a href="{{ $p['name'] === 'Cooperativa' ? 'mailto:contato@rocanossa.com.br' : route('register') }}" class="block w-full text-center px-4 py-3 text-sm font-semibold rounded-xl transition {{ $p['featured'] ? 'text-white bg-leaf-700 hover:bg-leaf-800 shadow-md shadow-leaf-700/20' : 'text-leaf-700 bg-leaf-50 hover:bg-leaf-100' }}">
                        {{ $p['cta'] }}
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section id="faq" class="py-24 bg-leaf-50">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <p class="text-sm font-semibold text-leaf-700 uppercase tracking-wider mb-2">Dúvidas</p>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-leaf-900">Perguntas que sempre chegam</h2>
        </div>
        <div class="space-y-3">
            @foreach([
                ['q'=>'Preciso instalar algo na fazenda?','a'=>'Não. A Roça Nossa abre direto no celular, no tablet ou no computador. Só precisa de internet e do navegador.'],
                ['q'=>'Como funciona o teste de 14 dias?','a'=>'Você cria sua conta e ganha 14 dias com tudo liberado. Sem cartão de crédito. No fim do teste, escolhe um plano ou para por aqui, fica de boas.'],
                ['q'=>'Eu trabalho só com café. Serve pra mim?','a'=>'Sim. A Roça Nossa nasceu na secagem de café e tem tudo pronto: cliente, secagem multi-produtor, comissão, rendimento, PDF da secagem. E ainda vai crescer junto com a roça.'],
                ['q'=>'Posso ter mais de uma roça?','a'=>'Cada conta cuida de uma fazenda. Se você tem mais de uma propriedade ou uma cooperativa, fala com a gente sobre o plano Cooperativa.'],
                ['q'=>'E os dados, ficam seguros?','a'=>'Sim. Cada roça tem seu espaço isolado, ninguém vê o que é seu. Tem auditoria de tudo, backup diário e conexão criptografada (HTTPS).'],
                ['q'=>'Como pago?','a'=>'Cartão de crédito mensal pela Asaas. A nota fiscal cai no seu e-mail e você cancela quando quiser, sem multa.'],
            ] as $f)
                <details class="group bg-white rounded-xl border border-leaf-100 overflow-hidden">
                    <summary class="flex items-center justify-between cursor-pointer px-5 py-4 list-none">
                        <span class="text-sm font-semibold text-leaf-900">{{ $f['q'] }}</span>
                        <svg class="w-4 h-4 text-leaf-500 transition group-open:rotate-180" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </summary>
                    <p class="px-5 pb-5 text-sm text-leaf-600 leading-relaxed">{{ $f['a'] }}</p>
                </details>
            @endforeach
        </div>
    </div>
</section>

<section class="py-20 bg-gradient-to-br from-leaf-700 to-leaf-900 relative overflow-hidden">
    <div class="absolute inset-0 opacity-10">
        <div class="absolute top-0 right-0 w-96 h-96 bg-white rounded-full -translate-y-1/2 translate-x-1/2"></div>
        <div class="absolute bottom-0 left-0 w-72 h-72 bg-white rounded-full translate-y-1/2 -translate-x-1/2"></div>
    </div>
    <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-white">
        <h2 class="text-3xl sm:text-4xl font-extrabold mb-4">A roça é sua. A tecnologia, nossa.</h2>
        <p class="text-leaf-200 text-lg max-w-xl mx-auto mb-8">14 dias grátis pra você botar pra rodar. Sem cartão, sem complicação.</p>
        <a href="{{ route('register') }}" class="inline-flex items-center gap-2 px-8 py-4 text-base font-bold text-leaf-900 bg-white hover:bg-leaf-50 rounded-xl transition-all shadow-lg hover:-translate-y-0.5">
            Criar minha roça
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
        </a>
    </div>
</section>

<footer class="bg-leaf-900 text-leaf-200 py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid md:grid-cols-4 gap-8 mb-8">
            <div>
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-7 h-7 rounded-md bg-white/10 flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M7 20h10"/>
                            <path d="M12 20V9"/>
                            <path d="M12 14c-3 0-5-2-5-5 3 0 5 2 5 5z"/>
                            <path d="M12 11c2.5 0 5-1.5 5-5-2.5 0-5 1.5-5 5z"/>
                        </svg>
                    </div>
                    <span class="font-bold text-white">Roça Nossa</span>
                </div>
                <p class="text-sm text-leaf-300">A tecnologia que entende a roça.</p>
            </div>
            <div>
                <p class="text-xs uppercase font-bold tracking-wider text-leaf-400 mb-3">Produto</p>
                <ul class="space-y-2 text-sm">
                    <li><a href="#features" class="hover:text-white transition">O que faz</a></li>
                    <li><a href="#planos" class="hover:text-white transition">Planos</a></li>
                    <li><a href="#faq" class="hover:text-white transition">Dúvidas</a></li>
                </ul>
            </div>
            <div>
                <p class="text-xs uppercase font-bold tracking-wider text-leaf-400 mb-3">Conta</p>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ route('login') }}" class="hover:text-white transition">Entrar</a></li>
                    <li><a href="{{ route('register') }}" class="hover:text-white transition">Criar minha roça</a></li>
                </ul>
            </div>
            <div>
                <p class="text-xs uppercase font-bold tracking-wider text-leaf-400 mb-3">Legal</p>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ url('/termos') }}" class="hover:text-white transition">Termos de uso</a></li>
                    <li><a href="{{ url('/privacidade') }}" class="hover:text-white transition">Privacidade</a></li>
                </ul>
            </div>
        </div>
        <div class="pt-6 border-t border-white/10 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-leaf-400">
            <p>© {{ date('Y') }} Roça Nossa. Todos os direitos reservados.</p>
            <p>Feito por quem entende de roça, pra quem trabalha na roça</p>
        </div>
    </div>
</footer>

</body>
</html>
