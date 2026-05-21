@extends('layouts.app')

@section('title', $farm->nome . ' (ROOT)')

@section('content')
@php
    $sub = $farm->subscription;
    $currentStatus = $sub?->status ?? $farm->status;
    $tone = match($currentStatus) {
        'partner'   => ['bg-purple-100', 'text-purple-700', 'border-purple-300'],
        'active'    => ['bg-emerald-100', 'text-emerald-700', 'border-emerald-300'],
        'trial'     => ['bg-amber-100', 'text-amber-700', 'border-amber-300'],
        'past_due'  => ['bg-orange-100', 'text-orange-700', 'border-orange-300'],
        'blocked'   => ['bg-rose-100', 'text-rose-700', 'border-rose-300'],
        'canceled'  => ['bg-gray-100', 'text-gray-700', 'border-gray-300'],
        default     => ['bg-gray-100', 'text-gray-700', 'border-gray-300'],
    };
@endphp

<div class="flex items-start justify-between mb-6 gap-4 flex-wrap">
    <div>
        <p class="text-xs text-leaf-500 mb-1">
            <span class="text-amber-700 font-semibold">Painel ROOT</span> ·
            <a href="{{ route('admin.fazendas.index') }}" class="hover:underline">Fazendas</a>
        </p>
        <div class="flex items-center gap-2">
            <h1 class="text-2xl font-bold text-leaf-900">{{ $farm->nome }}</h1>
            <span class="inline-block px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $tone[0] }} {{ $tone[1] }}">
                {{ $statuses[$currentStatus] ?? $currentStatus }}
            </span>
        </div>
        <p class="text-xs text-leaf-500 mt-1">
            Cadastrada em {{ $farm->created_at->format('d/m/Y') }} · slug: <code class="bg-leaf-100 px-1 rounded">{{ $farm->slug }}</code>
        </p>
    </div>
</div>

{{-- Contadores --}}
<div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-3 mb-6">
    @foreach([
        ['Usuários', $counts['users']],
        ['Clientes', $counts['customers']],
        ['Saldo (kg)', number_format($counts['saldo_total_kg'], 0, ',', '.')],
        ['Secagens OK', $counts['secagens_concluidas']],
        ['Rascunhos', $counts['secagens_rascunho']],
        ['Movimentos', $counts['movimentos']],
        ['Despesas', $counts['despesas']],
    ] as $card)
        <div class="bg-white rounded-xl border border-leaf-100 shadow-sm p-4">
            <p class="text-[10px] uppercase tracking-wider text-leaf-500 font-semibold">{{ $card[0] }}</p>
            <p class="text-xl font-bold text-leaf-900 mt-1">{{ $card[1] }}</p>
        </div>
    @endforeach
</div>

<div class="grid lg:grid-cols-3 gap-6">
    {{-- Coluna esquerda: dados + usuários --}}
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-xl border border-leaf-100 shadow-sm p-6">
            <h2 class="text-sm font-bold text-leaf-900 uppercase tracking-wider mb-4">Dados da fazenda</h2>
            <dl class="grid sm:grid-cols-2 gap-4 text-sm">
                <div>
                    <dt class="text-xs uppercase tracking-wider text-leaf-500 font-semibold">Telefone</dt>
                    <dd class="text-leaf-900 mt-1">{{ $farm->telefone ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wider text-leaf-500 font-semibold">Localização</dt>
                    <dd class="text-leaf-900 mt-1">{{ $farm->cidade ?? '—' }}{{ $farm->estado ? '/' . $farm->estado : '' }}</dd>
                </div>
            </dl>
        </div>

        <div class="bg-white rounded-xl border border-leaf-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-leaf-100">
                <h2 class="text-sm font-bold text-leaf-900 uppercase tracking-wider">Usuários ({{ $farm->users->count() }})</h2>
            </div>
            <table class="w-full text-sm">
                <thead class="bg-leaf-50/50 text-leaf-600 text-xs uppercase tracking-wider">
                    <tr>
                        <th class="text-left px-6 py-2 font-semibold">Nome</th>
                        <th class="text-left px-6 py-2 font-semibold">E-mail</th>
                        <th class="text-left px-6 py-2 font-semibold">Papel</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-leaf-100">
                    @foreach($farm->users as $u)
                        <tr>
                            <td class="px-6 py-2 text-leaf-900">{{ $u->name }}</td>
                            <td class="px-6 py-2 text-leaf-600">{{ $u->email }}</td>
                            <td class="px-6 py-2">
                                @foreach($u->roles as $role)
                                    <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wider bg-leaf-100 text-leaf-700">{{ $role->name }}</span>
                                @endforeach
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Coluna direita: assinatura + transições --}}
    <div class="space-y-6">
        <div class="bg-white rounded-xl border-2 {{ $tone[2] }} shadow-sm p-6">
            <h2 class="text-sm font-bold text-leaf-900 uppercase tracking-wider mb-4">Plano</h2>

            <div class="mb-4">
                <p class="text-xs text-leaf-500">Status atual</p>
                <p class="text-lg font-bold {{ $tone[1] }} mt-1">{{ $statuses[$currentStatus] ?? $currentStatus }}</p>
            </div>

            @if($sub)
                <dl class="space-y-2 text-sm mb-4">
                    @if($sub->trial_ends_at)
                        <div>
                            <dt class="text-xs uppercase tracking-wider text-leaf-500 font-semibold">Trial até</dt>
                            <dd class="text-leaf-900">{{ $sub->trial_ends_at->format('d/m/Y') }}</dd>
                        </div>
                    @endif
                    @if($sub->current_period_end)
                        <div>
                            <dt class="text-xs uppercase tracking-wider text-leaf-500 font-semibold">Período atual até</dt>
                            <dd class="text-leaf-900">{{ $sub->current_period_end->format('d/m/Y') }}</dd>
                        </div>
                    @endif
                    @if($sub->asaas_subscription_id)
                        <div>
                            <dt class="text-xs uppercase tracking-wider text-leaf-500 font-semibold">ID Asaas</dt>
                            <dd class="text-leaf-900 text-xs font-mono">{{ $sub->asaas_subscription_id }}</dd>
                        </div>
                    @endif
                </dl>
            @endif

            <div class="border-t border-leaf-100 pt-4">
                <p class="text-xs uppercase tracking-wider text-leaf-500 font-semibold mb-3">Mudar plano</p>
                <div class="space-y-2">
                    @foreach($transitions as $target)
                        @php
                            $targetTone = match($target) {
                                'partner'   => 'bg-purple-700 hover:bg-purple-800',
                                'active'    => 'bg-emerald-700 hover:bg-emerald-800',
                                'trial'     => 'bg-amber-600 hover:bg-amber-700',
                                'blocked'   => 'bg-rose-700 hover:bg-rose-800',
                                'canceled'  => 'bg-gray-700 hover:bg-gray-800',
                                default     => 'bg-leaf-700 hover:bg-leaf-800',
                            };
                            $confirmText = match($target) {
                                'partner'  => 'Conceder plano parceiro? Acesso total sem cobrança nem expiração.',
                                'active'   => 'Marcar como ativa? Assume pagamento em dia.',
                                'trial'    => 'Voltar pra trial? Renova o período de teste a partir de hoje.',
                                'blocked'  => 'Bloquear esta fazenda? Acesso suspenso até reativar.',
                                'canceled' => 'Cancelar a assinatura? A fazenda fica sem acesso.',
                                'past_due' => 'Marcar como em atraso?',
                                default    => 'Mudar pra ' . $statuses[$target] . '?',
                            };
                        @endphp
                        <form method="POST" action="{{ route('admin.fazendas.plano', $farm) }}"
                              data-confirm="Mudar plano pra {{ $statuses[$target] }}?"
                              data-confirm-text="{{ $confirmText }}"
                              data-confirm-yes="Sim, aplicar">
                            @csrf @method('PUT')
                            <input type="hidden" name="status" value="{{ $target }}">
                            <button type="submit" class="w-full px-3 py-2 text-xs font-bold text-white {{ $targetTone }} rounded-lg transition">
                                → {{ $statuses[$target] }}
                            </button>
                        </form>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
