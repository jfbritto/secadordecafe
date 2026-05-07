<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>secadordecafe — Gestão de fazendas e secagem de café</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.svg') }}">
    <link rel="canonical" href="{{ url('/') }}">
    <meta name="description" content="Plataforma SaaS para gestão de fazendas de café — clientes, secagens multi-cliente, controle de saldos, financeiro e dashboards. Teste grátis por 14 dias.">
    <meta name="keywords" content="gestão de fazenda café, secagem de café, controle de café, software para fazenda, planilha secagem café, gestão de produtores, sistema cafeicultura">
    <meta name="theme-color" content="#5a3a22">

    <meta property="og:type" content="website">
    <meta property="og:title" content="secadordecafe — Gestão de fazendas e secagem de café">
    <meta property="og:description" content="A planilha de secagem da sua fazenda virou software. Controle clientes, secagens multi-cliente, comissões e despesas em uma só plataforma.">
    <meta property="og:locale" content="pt_BR">

    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "secadordecafe",
        "applicationCategory": "BusinessApplication",
        "operatingSystem": "Web",
        "url": "{{ url('/') }}",
        "description": "Plataforma SaaS multi-tenant para gestão de fazendas e secagem de café.",
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
        tailwind.config = { theme: { extend: { colors: { coffee: {
            50:'#faf6f1',100:'#f1e6d6',200:'#e1c8a4',300:'#cca572',400:'#a87a47',
            500:'#8a5a2f',600:'#6e4322',700:'#5a3a22',800:'#3f2814',900:'#2b1c0e',
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
<body class="bg-white text-coffee-900">

<header class="sticky top-0 z-40 bg-white/85 backdrop-blur-lg border-b border-coffee-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <a href="/" class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-coffee-600 to-coffee-800 flex items-center justify-center shadow-md shadow-coffee-700/30">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 8h1a4 4 0 010 8h-1"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 8h14v9a4 4 0 01-4 4H7a4 4 0 01-4-4V8z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 4l1 2M11 4l1 2M15 4l1 2"/>
                    </svg>
                </div>
                <span class="text-lg font-bold text-coffee-900">secadordecafe</span>
            </a>
            <nav class="hidden md:flex items-center gap-7 text-sm font-medium text-coffee-600">
                <a href="#features" class="hover:text-coffee-900 transition">Funcionalidades</a>
                <a href="#planos" class="hover:text-coffee-900 transition">Planos</a>
                <a href="#como-funciona" class="hover:text-coffee-900 transition">Como funciona</a>
                <a href="#faq" class="hover:text-coffee-900 transition">FAQ</a>
            </nav>
            <div class="flex items-center gap-2">
                @auth
                    <a href="{{ route('dashboard') }}" class="px-4 py-2 text-sm font-semibold text-white bg-coffee-700 hover:bg-coffee-800 rounded-lg transition shadow-sm">Painel</a>
                @else
                    <a href="{{ route('login') }}" class="hidden sm:inline-block px-4 py-2 text-sm font-medium text-coffee-600 hover:text-coffee-900 transition">Entrar</a>
                    <a href="{{ route('register') }}" class="px-4 py-2 text-sm font-semibold text-white bg-coffee-700 hover:bg-coffee-800 rounded-lg transition shadow-sm shadow-coffee-700/20">Criar fazenda</a>
                @endauth
            </div>
        </div>
    </div>
</header>

<section class="relative overflow-hidden py-20 sm:py-28">
    <div class="absolute inset-0 bg-gradient-to-br from-coffee-50 via-white to-amber-50/40"></div>
    <div class="absolute top-20 right-10 w-72 h-72 bg-coffee-200 rounded-full opacity-30 blur-3xl float-1"></div>
    <div class="absolute bottom-10 left-10 w-96 h-96 bg-amber-200 rounded-full opacity-20 blur-3xl float-2"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1.5 text-xs font-semibold text-coffee-700 bg-coffee-100 rounded-full mb-6">
                    <span class="w-2 h-2 bg-coffee-600 rounded-full animate-pulse"></span>
                    Plataforma SaaS para cafeicultura
                </div>
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold leading-[1.05] tracking-tight mb-6">
                    A planilha da sua secagem
                    <span class="bg-gradient-to-r from-coffee-700 to-amber-700 bg-clip-text text-transparent">virou software.</span>
                </h1>
                <p class="text-lg text-coffee-600 leading-relaxed mb-8 max-w-xl">
                    Controle clientes, saldos de café, secagens multi-cliente, comissões e despesas em uma única
                    plataforma. Cálculos automáticos, histórico auditável e acesso de qualquer lugar.
                </p>
                <div class="flex flex-col sm:flex-row gap-3">
                    <a href="{{ route('register') }}" class="inline-flex items-center justify-center gap-2 px-8 py-4 text-base font-bold text-white bg-gradient-to-r from-coffee-700 to-coffee-800 hover:from-coffee-800 hover:to-coffee-900 rounded-xl transition-all shadow-lg shadow-coffee-700/30 hover:-translate-y-0.5">
                        Teste grátis por 14 dias
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                    </a>
                    <a href="#planos" class="inline-flex items-center justify-center gap-2 px-6 py-4 text-sm font-semibold text-coffee-700 bg-white border border-coffee-200 hover:border-coffee-400 rounded-xl transition-all">
                        Ver planos
                    </a>
                </div>
                <p class="mt-4 text-xs text-coffee-500">Sem cartão de crédito no trial. Cancele quando quiser.</p>
            </div>

            <div class="hidden lg:block relative">
                <div class="relative bg-white rounded-2xl shadow-2xl shadow-coffee-700/20 border border-coffee-100 overflow-hidden">
                    <div class="bg-gradient-to-r from-coffee-700 to-coffee-800 px-6 py-3 flex items-center gap-2">
                        <div class="flex gap-1.5">
                            <div class="w-3 h-3 rounded-full bg-white/30"></div>
                            <div class="w-3 h-3 rounded-full bg-white/30"></div>
                            <div class="w-3 h-3 rounded-full bg-white/30"></div>
                        </div>
                        <span class="text-white/80 text-xs ml-3 font-mono">secadordecafe.com.br/secagens/12</span>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs text-coffee-500 font-semibold uppercase tracking-wider">Secagem #12</p>
                                <p class="text-lg font-bold text-coffee-900">07/05/2026 · Secador 1</p>
                            </div>
                            <span class="px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-700 text-xs font-bold">CONCLUÍDA</span>
                        </div>
                        <div class="bg-coffee-50/50 rounded-xl overflow-hidden">
                            <table class="w-full text-xs">
                                <thead class="bg-coffee-100/60 text-coffee-700">
                                    <tr>
                                        <th class="text-left p-2 font-semibold">Cliente</th>
                                        <th class="text-right p-2 font-semibold">Recebido</th>
                                        <th class="text-right p-2 font-semibold">Seco</th>
                                        <th class="text-right p-2 font-semibold">Líquido</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="border-b border-coffee-100">
                                        <td class="p-2 text-coffee-900 font-medium">João Almeida</td>
                                        <td class="p-2 text-right text-coffee-700">600 kg</td>
                                        <td class="p-2 text-right text-coffee-700">360 kg</td>
                                        <td class="p-2 text-right font-bold text-coffee-800">342 kg</td>
                                    </tr>
                                    <tr class="border-b border-coffee-100">
                                        <td class="p-2 text-coffee-900 font-medium">Carlos Souza</td>
                                        <td class="p-2 text-right text-coffee-700">300 kg</td>
                                        <td class="p-2 text-right text-coffee-700">182 kg</td>
                                        <td class="p-2 text-right font-bold text-coffee-800">173 kg</td>
                                    </tr>
                                    <tr>
                                        <td class="p-2 text-coffee-900 font-medium">Pedro Lima</td>
                                        <td class="p-2 text-right text-coffee-700">200 kg</td>
                                        <td class="p-2 text-right text-coffee-700">125 kg</td>
                                        <td class="p-2 text-right font-bold text-coffee-800">119 kg</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="grid grid-cols-3 gap-2">
                            <div class="bg-coffee-50 rounded-lg p-2.5 text-center"><p class="text-base font-bold text-coffee-700">1.100</p><p class="text-[10px] text-coffee-500 uppercase tracking-wider">Recebido</p></div>
                            <div class="bg-emerald-50 rounded-lg p-2.5 text-center"><p class="text-base font-bold text-emerald-700">667</p><p class="text-[10px] text-emerald-500 uppercase tracking-wider">Seco</p></div>
                            <div class="bg-amber-50 rounded-lg p-2.5 text-center"><p class="text-base font-bold text-amber-700">60,6%</p><p class="text-[10px] text-amber-500 uppercase tracking-wider">Rendim.</p></div>
                        </div>
                    </div>
                </div>
                <div class="absolute -bottom-6 -left-6 w-24 h-24 bg-coffee-100 rounded-2xl rotate-12 opacity-60 -z-10"></div>
                <div class="absolute -top-4 -right-4 w-16 h-16 bg-amber-100 rounded-xl -rotate-12 opacity-60 -z-10"></div>
            </div>
        </div>
    </div>
</section>

<section id="features" class="py-24 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <p class="text-sm font-semibold text-coffee-700 uppercase tracking-wider mb-2">Funcionalidades</p>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-coffee-900 mb-4">Tudo que sua fazenda precisa</h2>
            <p class="text-lg text-coffee-500 max-w-2xl mx-auto">Pensado para a realidade brasileira da cafeicultura. Substitui planilhas, anotações e cadernos.</p>
        </div>

        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            @php
                $features = [
                    ['title'=>'Cadastro de clientes', 'desc'=>'Mantenha CPF/CNPJ, telefone, observações e saldo de café por produtor. Histórico completo em um clique.', 'icon'=>'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z', 'color'=>'coffee'],
                    ['title'=>'Secagens multi-cliente', 'desc'=>'Uma secagem com vários produtores. Quantidade recebida, quantidade seca, comissão e líquido por linha — totalizado automaticamente.', 'icon'=>'M5 8h14M5 12h14M5 16h14M4 4h16a1 1 0 011 1v14a1 1 0 01-1 1H4a1 1 0 01-1-1V5a1 1 0 011-1z', 'color'=>'amber'],
                    ['title'=>'Saldos protegidos', 'desc'=>'O sistema nunca permite saldo negativo. Toda alteração gera movimentação auditável — entrada, saída, ajuste ou secagem.', 'icon'=>'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', 'color'=>'emerald'],
                    ['title'=>'Cálculos automáticos', 'desc'=>'Rendimento (seco / recebido), comissão em kg e saldo líquido calculados em tempo real. Chega de calculadora.', 'icon'=>'M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z', 'color'=>'sky'],
                    ['title'=>'Controle financeiro', 'desc'=>'Despesas com categorias (combustível, manutenção, mão de obra…), filtros por período e totalizadores prontos para o relatório.', 'icon'=>'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'color'=>'rose'],
                    ['title'=>'Multi-fazenda', 'desc'=>'Cada fazenda tem seu próprio espaço seguro. Equipe com permissões — admin, operador, financeiro, visualizador.', 'icon'=>'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4', 'color'=>'indigo'],
                    ['title'=>'Convites por e-mail', 'desc'=>'Adicione operadores, financeiro ou visualizadores em segundos. Token seguro com validade — sem senha compartilhada.', 'icon'=>'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z', 'color'=>'cyan'],
                    ['title'=>'PDF de secagem', 'desc'=>'Imprima ou compartilhe o relatório completo de cada secagem em PDF — pronto para entregar ao produtor.', 'icon'=>'M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3', 'color'=>'purple'],
                    ['title'=>'Auditoria completa', 'desc'=>'Toda alteração registrada com data, hora e responsável. Confiança total no que aconteceu na fazenda.', 'icon'=>'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'color'=>'teal'],
                ];
                $cm = [
                    'coffee'=>['bg'=>'bg-coffee-100','text'=>'text-coffee-700','hover'=>'group-hover:bg-coffee-700','htext'=>'group-hover:text-white'],
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
                <div class="group p-7 rounded-2xl border border-coffee-100 hover:border-coffee-200 hover:shadow-lg transition-all">
                    <div class="w-11 h-11 rounded-xl {{ $c['bg'] }} {{ $c['hover'] }} flex items-center justify-center mb-4 transition-colors">
                        <svg class="w-5 h-5 {{ $c['text'] }} {{ $c['htext'] }} transition-colors" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $f['icon'] }}"/>
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-coffee-900 mb-1.5">{{ $f['title'] }}</h3>
                    <p class="text-sm text-coffee-500 leading-relaxed">{{ $f['desc'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section id="como-funciona" class="py-24 bg-coffee-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <p class="text-sm font-semibold text-coffee-700 uppercase tracking-wider mb-2">Como funciona</p>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-coffee-900 mb-4">Três passos para começar</h2>
        </div>
        <div class="grid md:grid-cols-3 gap-6">
            @foreach([
                ['n'=>'01','t'=>'Cadastre sua fazenda','d'=>'Crie sua conta em menos de 1 minuto e comece com 14 dias de teste grátis. Sem cartão.'],
                ['n'=>'02','t'=>'Importe os clientes','d'=>'Adicione produtores com saldo inicial de café. Convide sua equipe pelos seus e-mails.'],
                ['n'=>'03','t'=>'Lance secagens e acompanhe','d'=>'Cadastre cada secagem com múltiplos clientes. O sistema cuida dos cálculos e do extrato.'],
            ] as $step)
                <div class="bg-white rounded-2xl border border-coffee-100 p-7 shadow-sm">
                    <div class="text-5xl font-extrabold bg-gradient-to-br from-coffee-500 to-coffee-700 bg-clip-text text-transparent mb-3">{{ $step['n'] }}</div>
                    <h3 class="text-lg font-bold text-coffee-900 mb-2">{{ $step['t'] }}</h3>
                    <p class="text-sm text-coffee-500">{{ $step['d'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section id="planos" class="py-24 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <p class="text-sm font-semibold text-coffee-700 uppercase tracking-wider mb-2">Planos</p>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-coffee-900 mb-4">Comece grátis. Pague conforme cresce.</h2>
            <p class="text-lg text-coffee-500 max-w-2xl mx-auto">14 dias de teste em qualquer plano. Cobrança mensal, cancele quando quiser.</p>
        </div>

        <div class="grid md:grid-cols-3 gap-6 max-w-5xl mx-auto">
            @php
                $plans = [
                    ['name'=>'Trial','price'=>'Grátis','period'=>'14 dias','cta'=>'Começar agora','featured'=>false,'features'=>['Acesso completo','1 fazenda','Sem cartão de crédito','Suporte por e-mail']],
                    ['name'=>'Pro','price'=>'R$ 99,90','period'=>'/mês','cta'=>'Começar agora','featured'=>true,'features'=>['1 fazenda','Usuários ilimitados','Clientes ilimitados','Secagens ilimitadas','PDF de relatórios','Auditoria completa','Suporte prioritário']],
                    ['name'=>'Enterprise','price'=>'Sob medida','period'=>'','cta'=>'Falar com vendas','featured'=>false,'features'=>['Múltiplas fazendas','SLA dedicado','Importação de planilhas','Treinamento da equipe','Integrações sob medida']],
                ];
            @endphp
            @foreach($plans as $p)
                <div class="rounded-2xl border {{ $p['featured'] ? 'border-coffee-700 shadow-2xl shadow-coffee-700/15 ring-2 ring-coffee-700' : 'border-coffee-100 shadow-sm' }} bg-white p-7 relative">
                    @if($p['featured'])
                        <span class="absolute -top-3 left-1/2 -translate-x-1/2 px-3 py-1 rounded-full bg-coffee-700 text-white text-[10px] font-bold uppercase tracking-wider">Mais popular</span>
                    @endif
                    <h3 class="text-lg font-bold text-coffee-900">{{ $p['name'] }}</h3>
                    <div class="mt-4 mb-5">
                        <span class="text-4xl font-extrabold text-coffee-900">{{ $p['price'] }}</span>
                        <span class="text-sm text-coffee-500 ml-1">{{ $p['period'] }}</span>
                    </div>
                    <ul class="space-y-2.5 mb-7 text-sm text-coffee-700">
                        @foreach($p['features'] as $f)
                            <li class="flex items-start gap-2">
                                <svg class="w-4 h-4 text-emerald-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                {{ $f }}
                            </li>
                        @endforeach
                    </ul>
                    <a href="{{ $p['name'] === 'Enterprise' ? 'mailto:contato@secadordecafe.com.br' : route('register') }}" class="block w-full text-center px-4 py-3 text-sm font-semibold rounded-xl transition {{ $p['featured'] ? 'text-white bg-coffee-700 hover:bg-coffee-800 shadow-md shadow-coffee-700/20' : 'text-coffee-700 bg-coffee-50 hover:bg-coffee-100' }}">
                        {{ $p['cta'] }}
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section id="faq" class="py-24 bg-coffee-50">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <p class="text-sm font-semibold text-coffee-700 uppercase tracking-wider mb-2">FAQ</p>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-coffee-900">Perguntas frequentes</h2>
        </div>
        <div class="space-y-3">
            @foreach([
                ['q'=>'Preciso instalar algo na fazenda?','a'=>'Não. O sistema é 100% online — basta um celular, tablet ou computador com internet. Funciona no navegador.'],
                ['q'=>'Como funciona o teste grátis?','a'=>'Você cria sua conta e tem 14 dias com acesso completo. Sem cartão de crédito. Ao final do trial, escolhe um plano ou para por aqui.'],
                ['q'=>'Posso ter mais de uma fazenda?','a'=>'Cada conta gerencia uma fazenda. Para múltiplas fazendas, fale com a gente sobre o plano Enterprise.'],
                ['q'=>'Os dados ficam seguros?','a'=>'Cada fazenda tem seus dados isolados. Auditoria completa de toda alteração. Backups diários, conexão criptografada (HTTPS).'],
                ['q'=>'O sistema substitui minha planilha?','a'=>'Sim. Cobre todos os controles que você tem hoje na planilha de secagem (data, cliente, secador, recebido, seco, comissão, saldo) e ainda mais.'],
                ['q'=>'Como sou cobrado?','a'=>'Cartão de crédito recorrente via Asaas. Você recebe nota fiscal por e-mail e pode cancelar quando quiser.'],
            ] as $f)
                <details class="group bg-white rounded-xl border border-coffee-100 overflow-hidden">
                    <summary class="flex items-center justify-between cursor-pointer px-5 py-4 list-none">
                        <span class="text-sm font-semibold text-coffee-900">{{ $f['q'] }}</span>
                        <svg class="w-4 h-4 text-coffee-500 transition group-open:rotate-180" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </summary>
                    <p class="px-5 pb-5 text-sm text-coffee-600 leading-relaxed">{{ $f['a'] }}</p>
                </details>
            @endforeach
        </div>
    </div>
</section>

<section class="py-20 bg-gradient-to-br from-coffee-700 to-coffee-900 relative overflow-hidden">
    <div class="absolute inset-0 opacity-10">
        <div class="absolute top-0 right-0 w-96 h-96 bg-white rounded-full -translate-y-1/2 translate-x-1/2"></div>
        <div class="absolute bottom-0 left-0 w-72 h-72 bg-white rounded-full translate-y-1/2 -translate-x-1/2"></div>
    </div>
    <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-white">
        <h2 class="text-3xl sm:text-4xl font-extrabold mb-4">Comece a controlar sua fazenda hoje</h2>
        <p class="text-coffee-200 text-lg max-w-xl mx-auto mb-8">14 dias de teste grátis. Sem cartão de crédito. Cancele quando quiser.</p>
        <a href="{{ route('register') }}" class="inline-flex items-center gap-2 px-8 py-4 text-base font-bold text-coffee-900 bg-white hover:bg-coffee-50 rounded-xl transition-all shadow-lg hover:-translate-y-0.5">
            Criar minha fazenda
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
        </a>
    </div>
</section>

<footer class="bg-coffee-900 text-coffee-200 py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid md:grid-cols-4 gap-8 mb-8">
            <div>
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-7 h-7 rounded-md bg-white/10 flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 8h1a4 4 0 010 8h-1M3 8h14v9a4 4 0 01-4 4H7a4 4 0 01-4-4V8z"/>
                        </svg>
                    </div>
                    <span class="font-bold text-white">secadordecafe</span>
                </div>
                <p class="text-sm text-coffee-300">Gestão de fazendas e secagem de café.</p>
            </div>
            <div>
                <p class="text-xs uppercase font-bold tracking-wider text-coffee-400 mb-3">Produto</p>
                <ul class="space-y-2 text-sm">
                    <li><a href="#features" class="hover:text-white transition">Funcionalidades</a></li>
                    <li><a href="#planos" class="hover:text-white transition">Planos</a></li>
                    <li><a href="#faq" class="hover:text-white transition">FAQ</a></li>
                </ul>
            </div>
            <div>
                <p class="text-xs uppercase font-bold tracking-wider text-coffee-400 mb-3">Conta</p>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ route('login') }}" class="hover:text-white transition">Entrar</a></li>
                    <li><a href="{{ route('register') }}" class="hover:text-white transition">Criar fazenda</a></li>
                </ul>
            </div>
            <div>
                <p class="text-xs uppercase font-bold tracking-wider text-coffee-400 mb-3">Legal</p>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ url('/termos') }}" class="hover:text-white transition">Termos de uso</a></li>
                    <li><a href="{{ url('/privacidade') }}" class="hover:text-white transition">Privacidade</a></li>
                </ul>
            </div>
        </div>
        <div class="pt-6 border-t border-white/10 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-coffee-400">
            <p>© {{ date('Y') }} secadordecafe. Todos os direitos reservados.</p>
            <p>Feito para a cafeicultura brasileira</p>
        </div>
    </div>
</footer>

</body>
</html>
