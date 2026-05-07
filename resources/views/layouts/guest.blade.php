<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Acesso') — secadordecafe</title>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'><path d='M17 8h1a4 4 0 010 8h-1m0-8H3v9a4 4 0 004 4h6a4 4 0 004-4V8z' stroke='%235a3a22' stroke-width='1.5' fill='none' stroke-linecap='round' stroke-linejoin='round'/></svg>">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { theme: { extend: { colors: { coffee: {
            50:'#faf6f1',100:'#f1e6d6',200:'#e1c8a4',300:'#cca572',400:'#a87a47',
            500:'#8a5a2f',600:'#6e4322',700:'#5a3a22',800:'#3f2814',900:'#2b1c0e',
        } }, fontFamily: { sans: ['Inter','system-ui','sans-serif'] } } } }
    </script>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', system-ui, sans-serif; }</style>
</head>
<body class="min-h-screen bg-gradient-to-br from-coffee-50 via-white to-coffee-100 flex flex-col">

    <header class="px-6 py-5">
        <a href="/" class="inline-flex items-center gap-2.5">
            <div class="w-9 h-9 rounded-lg bg-coffee-700 flex items-center justify-center shadow-md shadow-coffee-700/20">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 8h1a4 4 0 010 8h-1"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 8h14v9a4 4 0 01-4 4H7a4 4 0 01-4-4V8z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 4l1 2M11 4l1 2M15 4l1 2"/>
                </svg>
            </div>
            <span class="text-xl font-bold text-coffee-900">secadordecafe</span>
        </a>
    </header>

    <main class="flex-1 flex items-center justify-center px-4 py-8">
        <div class="w-full max-w-md">
            @yield('content')
        </div>
    </main>

    <footer class="text-center text-xs text-coffee-500 py-6">
        © {{ date('Y') }} secadordecafe · gestão de fazendas e secagem de café
    </footer>
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
