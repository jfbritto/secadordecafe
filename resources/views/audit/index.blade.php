@extends('layouts.app')
@section('title', 'Auditoria')
@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-bold text-leaf-900">Auditoria</h1>
    <p class="text-sm text-leaf-500 mt-0.5">Histórico de tudo que foi cadastrado, editado ou excluído na fazenda. Útil pra conferir o que aconteceu e quando.</p>
</div>

<div x-data="{ open: false, diff: [], title: '', subtitle: '' }">
    <div class="bg-white rounded-2xl border border-leaf-100 shadow-sm overflow-hidden">
        @forelse($activities as $a)
            @php
                $h = $a->humanized;
                $eventCls = match($h['event']) {
                    'created' => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-700', 'verb' => 'cadastrou'],
                    'updated' => ['bg' => 'bg-sky-100', 'text' => 'text-sky-700', 'verb' => 'editou'],
                    'deleted' => ['bg' => 'bg-rose-100', 'text' => 'text-rose-700', 'verb' => 'excluiu'],
                    default   => ['bg' => 'bg-leaf-100', 'text' => 'text-leaf-700', 'verb' => $h['action']],
                };
            @endphp

            <div class="px-4 sm:px-6 py-4 border-b border-leaf-100 last:border-b-0 flex items-start gap-4 hover:bg-leaf-50/30 transition">
                {{-- Ícone da entidade --}}
                <div class="w-10 h-10 rounded-lg {{ $eventCls['bg'] }} flex-shrink-0 flex items-center justify-center">
                    @if($h['icon'])
                        <svg class="w-5 h-5 {{ $eventCls['text'] }}" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $h['icon'] }}"/>
                        </svg>
                    @else
                        <span class="text-xs font-bold {{ $eventCls['text'] }}">?</span>
                    @endif
                </div>

                {{-- Conteúdo --}}
                <div class="flex-1 min-w-0">
                    <p class="text-sm text-leaf-900 leading-snug">
                        <strong>{{ $h['actor'] }}</strong>
                        <span class="text-leaf-600">{{ $eventCls['verb'] }}</span>
                        <span class="text-leaf-500">·</span>
                        <span class="font-medium">{{ $h['entity_label'] }}</span>
                        <strong class="text-leaf-800">{{ $h['subject_name'] }}</strong>
                    </p>
                    <p class="text-xs text-leaf-500 mt-1">
                        {{ $h['when']->format('d/m/Y \à\s H:i') }} · {{ $h['when']->diffForHumans() }}
                    </p>
                </div>

                {{-- Botão detalhes (só pra updated com diff) --}}
                <div class="flex-shrink-0">
                    @if($h['has_diff'])
                        <button type="button"
                                x-on:click="open = true; diff = @js($a->diff_data); title = @js($h['entity_label'].' '.$h['subject_name']); subtitle = @js($h['actor'].' editou em '.$h['when']->format('d/m/Y H:i'))"
                                class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold text-leaf-700 bg-leaf-50 hover:bg-leaf-100 rounded-lg transition border border-leaf-200">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            Ver alterações
                        </button>
                    @endif
                </div>
            </div>
        @empty
            <div class="px-6 py-16 text-center text-leaf-500">
                Nenhum registro de atividade ainda. Conforme você usa o sistema, as ações aparecem aqui.
            </div>
        @endforelse
    </div>

    <div class="mt-4">{{ $activities->links() }}</div>

    {{-- Modal de diff --}}
    <div x-show="open" x-cloak
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
         @keydown.escape.window="open = false"
         @click.self="open = false">

        <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[85vh] flex flex-col"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">
            <div class="px-6 py-4 border-b border-leaf-100 flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <h2 class="text-lg font-bold text-leaf-900 truncate" x-text="title"></h2>
                    <p class="text-xs text-leaf-500 mt-0.5" x-text="subtitle"></p>
                </div>
                <button @click="open = false" class="flex-shrink-0 p-2 rounded-lg text-leaf-500 hover:bg-leaf-50 hover:text-leaf-900">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="overflow-y-auto p-6 flex-1">
                <template x-if="diff.length === 0">
                    <p class="text-sm text-leaf-500 text-center py-8">Sem alterações registradas.</p>
                </template>
                <template x-if="diff.length > 0">
                    <div class="space-y-3">
                        <template x-for="(row, i) in diff" :key="i">
                            <div class="border border-leaf-100 rounded-xl p-4">
                                <p class="text-xs font-bold text-leaf-500 uppercase tracking-wider mb-2" x-text="row.field"></p>
                                <div class="grid sm:grid-cols-2 gap-3 text-sm">
                                    <div class="bg-rose-50 border border-rose-100 rounded-lg p-3">
                                        <p class="text-[10px] font-bold text-rose-600 uppercase tracking-wider mb-1">De</p>
                                        <p class="text-rose-900 break-words whitespace-pre-wrap" x-text="row.old"></p>
                                    </div>
                                    <div class="bg-emerald-50 border border-emerald-100 rounded-lg p-3">
                                        <p class="text-[10px] font-bold text-emerald-600 uppercase tracking-wider mb-1">Para</p>
                                        <p class="text-emerald-900 break-words whitespace-pre-wrap" x-text="row.new"></p>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>
            </div>

            <div class="px-6 py-4 border-t border-leaf-100 flex justify-end">
                <button @click="open = false" class="px-5 py-2 text-sm font-semibold text-white bg-leaf-700 hover:bg-leaf-800 rounded-lg transition">Fechar</button>
            </div>
        </div>
    </div>
</div>

@endsection
