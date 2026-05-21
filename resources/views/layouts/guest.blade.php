<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Acesso') · Roça Nossa</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.svg') }}">
    <meta name="theme-color" content="#1e5631">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        tailwind.config = { theme: { extend: { colors: { leaf: {
            50:'#fdfbf4',100:'#f4ead4',200:'#d8c9a8',300:'#b3c49d',400:'#8db580',
            500:'#5da361',600:'#2d8a4a',700:'#1e5631',800:'#143a23',900:'#0a2415',
        } }, fontFamily: { sans: ['Inter','system-ui','sans-serif'] } } } }
    </script>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', system-ui, sans-serif; }</style>
</head>
<body class="min-h-screen bg-white">

<div class="min-h-screen flex flex-col lg:flex-row">

    {{-- COLUNA ESQUERDA: branding + features (oculta em mobile pra dar foco ao formulário) --}}
    <aside class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-leaf-700 via-leaf-800 to-leaf-900 text-white p-12 flex-col justify-between relative overflow-hidden">
        {{-- Decoração: círculos suaves no fundo --}}
        <div class="absolute top-20 -right-20 w-80 h-80 bg-leaf-600/20 rounded-full blur-3xl"></div>
        <div class="absolute bottom-40 -left-20 w-96 h-96 bg-leaf-500/10 rounded-full blur-3xl"></div>

        <div class="relative z-10">
            {{-- Logo --}}
            <a href="/" class="inline-flex items-center gap-3 mb-16">
                <div class="w-12 h-12 rounded-xl bg-white/15 backdrop-blur-sm flex items-center justify-center">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M7 20h10"/>
                        <path d="M12 20V9"/>
                        <path d="M12 14c-3 0-5-2-5-5 3 0 5 2 5 5z"/>
                        <path d="M12 11c2.5 0 5-1.5 5-5-2.5 0-5 1.5-5 5z"/>
                    </svg>
                </div>
                <span class="text-2xl font-bold">Roça Nossa</span>
            </a>

            {{-- Headline --}}
            <h1 class="text-4xl xl:text-5xl font-extrabold leading-tight mb-4">
                @yield('promo_title')
                @hasSection('promo_title') @else
                    A roça é nossa.<br>
                    <span class="text-leaf-300">O controle também.</span>
                @endif
            </h1>
            <p class="text-leaf-200 text-lg mb-10 max-w-md">
                @yield('promo_subtitle')
                @hasSection('promo_subtitle') @else
                    Acompanhe seus produtores do plantio ao pagamento. Tudo na palma da mão.
                @endif
            </p>

            {{-- Features --}}
            <ul class="space-y-4">
                @hasSection('promo_features')
                    @yield('promo_features')
                @else
                    <li class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-full bg-white/15 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <span class="text-white pt-2">Saldo de cada produtor sempre certo, como conta de banco</span>
                    </li>
                    <li class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-full bg-white/15 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M15.362 5.214A8.252 8.252 0 0112 21 8.25 8.25 0 016.038 7.048 8.287 8.287 0 009 9.6a8.983 8.983 0 013.361-6.867 8.21 8.21 0 003 2.48z"/>
                            </svg>
                        </div>
                        <span class="text-white pt-2">Secagens com vários produtores juntos, totalizadas na hora</span>
                    </li>
                    <li class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-full bg-white/15 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
                            </svg>
                        </div>
                        <span class="text-white pt-2">Despesas e equipe organizadas no mesmo lugar</span>
                    </li>
                @endif
            </ul>
        </div>

        {{-- Copyright --}}
        <p class="relative z-10 text-leaf-300/70 text-xs mt-12">
            © {{ date('Y') }} Roça Nossa. Todos os direitos reservados.
        </p>
    </aside>

    {{-- COLUNA DIREITA: formulário --}}
    <main class="w-full lg:w-1/2 flex flex-col">
        {{-- Logo mobile (só aparece quando a coluna esquerda está oculta) --}}
        <div class="lg:hidden px-6 py-5 border-b border-leaf-100">
            <a href="/" class="inline-flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-lg bg-leaf-700 flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M7 20h10"/>
                        <path d="M12 20V9"/>
                        <path d="M12 14c-3 0-5-2-5-5 3 0 5 2 5 5z"/>
                        <path d="M12 11c2.5 0 5-1.5 5-5-2.5 0-5 1.5-5 5z"/>
                    </svg>
                </div>
                <span class="text-xl font-bold text-leaf-900">Roça Nossa</span>
            </a>
        </div>

        <div class="flex-1 flex items-center justify-center px-6 sm:px-12 py-10">
            <div class="w-full max-w-md">
                @yield('content')
            </div>
        </div>
    </main>
</div>

<script>
(function () {
    const masks = {
        cpfcnpj(v) {
            v = (v || '').replace(/\D/g, '').slice(0, 14);
            if (!v) return '';
            if (v.length <= 11) return v.replace(/(\d{3})(\d)/, '$1.$2').replace(/(\d{3})(\d)/, '$1.$2').replace(/(\d{3})(\d{1,2})$/, '$1-$2');
            return v.replace(/(\d{2})(\d)/, '$1.$2').replace(/(\d{3})(\d)/, '$1.$2').replace(/(\d{3})(\d)/, '$1/$2').replace(/(\d{4})(\d{1,2})$/, '$1-$2');
        },
        phone(v) {
            v = (v || '').replace(/\D/g, '').slice(0, 11);
            if (!v) return '';
            if (v.length <= 10) return v.replace(/(\d{2})(\d)/, '($1) $2').replace(/(\d{4})(\d{1,4})$/, '$1-$2');
            return v.replace(/(\d{2})(\d)/, '($1) $2').replace(/(\d{5})(\d{1,4})$/, '$1-$2');
        },
        uf(v) { return (v || '').replace(/[^a-zA-Z]/g, '').toUpperCase().slice(0, 2); },
    };
    document.addEventListener('input', function (e) {
        const t = e.target, m = t && t.dataset && t.dataset.mask;
        if (!m || !masks[m]) return;
        const before = t.value.length, pos = t.selectionStart;
        t.value = masks[m](t.value);
        try { const diff = t.value.length - before; t.setSelectionRange(pos + diff, pos + diff); } catch (_) {}
    });
    document.querySelectorAll('[data-mask]').forEach(el => { if (el.dataset.mask in masks && el.value) el.value = masks[el.dataset.mask](el.value); });
})();
</script>
</body>
</html>
