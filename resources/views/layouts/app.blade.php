<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name')) — Roça Nossa</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.svg') }}">
    <meta name="theme-color" content="#5a3a22">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        coffee: {
                            50:  '#faf6f1', 100: '#f1e6d6', 200: '#e1c8a4', 300: '#cca572',
                            400: '#a87a47', 500: '#8a5a2f', 600: '#6e4322', 700: '#5a3a22',
                            800: '#3f2814', 900: '#2b1c0e',
                        },
                    },
                    fontFamily: { sans: ['Inter', 'system-ui', 'sans-serif'] },
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @stack('head')
    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Inter', system-ui, sans-serif; }
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: #f5f1ea; }
        ::-webkit-scrollbar-thumb { background: #d6c9b6; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #a87a47; }
    </style>
</head>
<body class="bg-coffee-50 text-coffee-900">

@php
    $u = auth()->user();
    $farm = $u?->farm;
    $isRoot = (bool) $u?->isRoot();
    $isAdmin = $u && ($u->hasRole('admin') || $isRoot);
    $canFinanceiro = $u && ($u->hasAnyRole(['admin','financeiro','visualizador']) || $isRoot);
    $initials = collect(explode(' ', $u?->name ?? ''))->filter()->map(fn($w)=>strtoupper(substr($w,0,1)))->take(2)->implode('');
    $roleLabel = match(true) {
        $isRoot => 'Root',
        (bool) $u?->hasRole('admin') => 'Administrador',
        (bool) $u?->hasRole('operador') => 'Operador',
        (bool) $u?->hasRole('financeiro') => 'Financeiro',
        (bool) $u?->hasRole('visualizador') => 'Visualizador',
        default => '—',
    };
@endphp

<div class="flex h-screen overflow-hidden" x-data="{ sidebarOpen: false }">

    {{-- Backdrop mobile --}}
    <div x-show="sidebarOpen" x-cloak @click="sidebarOpen=false"
         x-transition:enter="transition-opacity duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         class="fixed inset-0 bg-black/50 z-40 lg:hidden"></div>

    {{-- Sidebar --}}
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
           class="fixed inset-y-0 left-0 lg:static z-50 w-64 flex-shrink-0 bg-coffee-700 text-white flex flex-col transition-transform duration-300">

        {{-- Header sidebar --}}
        <div class="px-5 pt-5 pb-4 border-b border-white/10 relative">
            <button @click="sidebarOpen=false" class="lg:hidden absolute top-3 right-3 p-1.5 rounded-md text-white/60 hover:text-white hover:bg-white/10 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-lg bg-white/15 flex items-center justify-center flex-shrink-0">
                    {{-- Logo Roça Nossa: broto saindo da terra --}}
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M7 20h10"/>
                        <path d="M12 20V9"/>
                        <path d="M12 14c-3 0-5-2-5-5 3 0 5 2 5 5z"/>
                        <path d="M12 11c2.5 0 5-1.5 5-5-2.5 0-5 1.5-5 5z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-bold leading-tight">Roça Nossa</p>
                    @if($farm)
                        <p class="text-[11px] text-white/60 truncate leading-tight mt-0.5">{{ $farm->nome }}</p>
                    @elseif($isRoot)
                        <p class="text-[11px] text-amber-200 truncate leading-tight mt-0.5">Painel ROOT</p>
                    @endif
                </div>
            </a>
            @if($farm && $farm->isOnTrial())
                <div class="mt-3 px-2 py-1.5 rounded-md bg-amber-400/20 border border-amber-300/30 text-[11px] text-amber-100 text-center">
                    Trial · até {{ $farm->trial_ends_at->format('d/m') }}
                </div>
            @endif
        </div>

        {{-- Nav --}}
        <nav class="flex-1 p-3 space-y-0.5 overflow-y-auto">
            @php
                $nav = function ($routeName, $label, $icon, $matchPattern = null) {
                    $active = request()->routeIs($matchPattern ?? $routeName);
                    $base = 'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition';
                    $cls = $active ? "$base bg-white/15 font-semibold text-white" : "$base text-white/80 hover:bg-white/10 hover:text-white";
                    echo '<a href="'.route($routeName).'" @click="sidebarOpen=false" class="'.$cls.'">';
                    echo '<svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="'.$icon.'"/></svg>';
                    echo '<span>'.e($label).'</span>';
                    echo '</a>';
                };
            @endphp

            <div class="px-3 pt-2 pb-1 text-[10px] font-semibold uppercase tracking-wider text-white/40">Geral</div>
            @php $nav('dashboard', 'Dashboard', 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'); @endphp

            @if(!$isRoot)
                <div class="px-3 pt-3 pb-1 text-[10px] font-semibold uppercase tracking-wider text-white/40">Operação</div>
                @php $nav('clientes.index', 'Clientes', 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z', 'clientes.*'); @endphp
                @php $nav('secagens.index', 'Secagens', 'M5 8h14M5 12h14M5 16h14M4 4h16a1 1 0 011 1v14a1 1 0 01-1 1H4a1 1 0 01-1-1V5a1 1 0 011-1z', 'secagens.*'); @endphp
                @php $nav('secadores.index', 'Secadores', 'M9 17v-2a4 4 0 014-4h6m-4-4l4 4-4 4M3 7v10a2 2 0 002 2h6a2 2 0 002-2v-2', 'secadores.*'); @endphp
                @php $nav('areas.index', 'Áreas', 'M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0zM15 11a3 3 0 11-6 0 3 3 0 016 0z', 'areas.*'); @endphp

                @if($canFinanceiro)
                    <div class="px-3 pt-3 pb-1 text-[10px] font-semibold uppercase tracking-wider text-white/40">Financeiro</div>
                    @php $nav('despesas.index', 'Despesas', 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'despesas.index'); @endphp
                    @php $nav('despesas.categorias.index', 'Categorias', 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z', 'despesas.categorias.*'); @endphp
                @endif

                @if($isAdmin)
                    <div class="px-3 pt-3 pb-1 text-[10px] font-semibold uppercase tracking-wider text-white/40">Administração</div>
                    @php $nav('usuarios.index', 'Usuários', 'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z', 'usuarios.*'); @endphp
                    @php $nav('fazenda.edit', 'Fazenda', 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h14a1 1 0 001-1V10', 'fazenda.*'); @endphp
                    @php $nav('assinatura.show', 'Assinatura', 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z', 'assinatura.*'); @endphp
                    @php $nav('auditoria.index', 'Auditoria', 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'auditoria.*'); @endphp
                @endif
            @endif
        </nav>

        {{-- User card --}}
        <div class="p-3 border-t border-white/10 flex-shrink-0">
            <div class="flex items-center gap-2.5 px-2 py-2 rounded-lg">
                <div class="w-9 h-9 rounded-full bg-white/15 flex items-center justify-center flex-shrink-0">
                    <span class="text-xs font-bold text-white">{{ $initials ?: '?' }}</span>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium leading-tight truncate">{{ $u?->name }}</p>
                    <p class="text-[10px] text-white/50 leading-tight">{{ $roleLabel }}</p>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="mt-1">
                @csrf
                <button type="submit" class="w-full flex items-center gap-2 px-3 py-2 rounded-lg text-xs text-white/60 hover:text-white hover:bg-white/10 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    Sair
                </button>
            </form>
        </div>
    </aside>

    {{-- Main column --}}
    <div class="flex-1 flex flex-col overflow-hidden min-w-0">
        {{-- Topbar --}}
        <header class="bg-white border-b border-coffee-100 px-4 sm:px-6 py-3 flex items-center gap-3 flex-shrink-0 shadow-sm">
            <button @click="sidebarOpen=true" class="lg:hidden p-2 rounded-md text-coffee-500 hover:text-coffee-700 hover:bg-coffee-50 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <h1 class="text-base font-semibold text-coffee-900 truncate flex-1">@yield('title', 'Painel')</h1>
            @if($farm)
                @php $statusCls = match($farm->status){'active'=>'bg-emerald-100 text-emerald-700','blocked'=>'bg-rose-100 text-rose-700','past_due'=>'bg-amber-100 text-amber-700', default=>'bg-amber-100 text-amber-700'}; @endphp
                <span class="hidden sm:inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold {{ $statusCls }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                    {{ strtoupper($farm->status) }}
                </span>
            @endif
        </header>

        {{-- Subscription warning --}}
        @if($farm && in_array($farm->status, ['past_due'], true))
            <div class="bg-amber-50 border-l-4 border-amber-400 text-amber-900 px-4 py-3 text-sm flex-shrink-0">
                <strong>Pagamento em atraso.</strong> Regularize sua assinatura em <a href="{{ route('assinatura.show') }}" class="underline font-semibold">Assinatura</a> para evitar bloqueio.
            </div>
        @endif

        {{-- Content --}}
        <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">
            <div class="max-w-7xl mx-auto">
                @yield('content')
            </div>
        </main>
    </div>
</div>

@stack('scripts')

{{-- Toasts via SweetAlert2: flash de sucesso e erro --}}
@if(session('flash') || session('error'))
<script>
document.addEventListener('DOMContentLoaded', function () {
    @if(session('flash'))
    Swal.fire({
        toast: true, position: 'top-end', icon: 'success',
        html: @json(session('flash')),
        showConfirmButton: false, timer: 4500, timerProgressBar: true,
        background: '#ecfdf5', iconColor: '#059669', color: '#065f46',
    });
    @endif
    @if(session('error'))
    Swal.fire({
        toast: true, position: 'top-end', icon: 'error',
        html: @json(session('error')),
        showConfirmButton: false, timer: 6000, timerProgressBar: true,
        background: '#fef2f2', iconColor: '#dc2626', color: '#991b1b',
    });
    @endif
});
</script>
@endif

{{-- Confirmação de ações via data-confirm + data-confirm-text --}}
<script>
document.addEventListener('submit', function (e) {
    const form = e.target;
    if (!(form instanceof HTMLFormElement)) return;
    const msg = form.dataset.confirm;
    if (!msg) return;
    if (form.dataset.confirmed === '1') return;
    e.preventDefault();
    Swal.fire({
        title: msg,
        text: form.dataset.confirmText || '',
        icon: form.dataset.confirmIcon || 'warning',
        showCancelButton: true,
        confirmButtonText: form.dataset.confirmYes || 'Sim, confirmar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: form.dataset.confirmDanger === '0' ? '#5a3a22' : '#dc2626',
        cancelButtonColor: '#a87a47',
        reverseButtons: true,
        focusCancel: true,
    }).then(function (r) {
        if (r.isConfirmed) {
            form.dataset.confirmed = '1';
            form.submit();
        }
    });
});
</script>

<script>
// Máscaras automáticas via data-mask="cpfcnpj"|"phone"|"uf"
(function () {
    const masks = {
        cpfcnpj(v) {
            v = (v || '').replace(/\D/g, '').slice(0, 14);
            if (!v) return '';
            if (v.length <= 11) {
                return v
                    .replace(/(\d{3})(\d)/, '$1.$2')
                    .replace(/(\d{3})(\d)/, '$1.$2')
                    .replace(/(\d{3})(\d{1,2})$/, '$1-$2');
            }
            return v
                .replace(/(\d{2})(\d)/, '$1.$2')
                .replace(/(\d{3})(\d)/, '$1.$2')
                .replace(/(\d{3})(\d)/, '$1/$2')
                .replace(/(\d{4})(\d{1,2})$/, '$1-$2');
        },
        phone(v) {
            v = (v || '').replace(/\D/g, '').slice(0, 11);
            if (!v) return '';
            if (v.length <= 10) {
                return v.replace(/(\d{2})(\d)/, '($1) $2').replace(/(\d{4})(\d{1,4})$/, '$1-$2');
            }
            return v.replace(/(\d{2})(\d)/, '($1) $2').replace(/(\d{5})(\d{1,4})$/, '$1-$2');
        },
        uf(v) { return (v || '').replace(/[^a-zA-Z]/g, '').toUpperCase().slice(0, 2); },
    };
    document.addEventListener('input', function (e) {
        const t = e.target;
        const m = t && t.dataset && t.dataset.mask;
        if (!m || !masks[m]) return;
        const before = t.value.length;
        const pos = t.selectionStart;
        t.value = masks[m](t.value);
        // Mantém o cursor próximo da posição original
        try {
            const diff = t.value.length - before;
            t.setSelectionRange(pos + diff, pos + diff);
        } catch (_) {}
    });
    // Aplica máscara no carregamento (campos pré-preenchidos)
    document.querySelectorAll('[data-mask]').forEach(function (el) {
        if (el.dataset.mask in masks && el.value) {
            el.value = masks[el.dataset.mask](el.value);
        }
    });
})();
</script>
</body>
</html>
