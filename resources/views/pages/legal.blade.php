@extends('layouts.guest')

@section('title', $title)

@section('content')
<article class="bg-white rounded-2xl shadow-xl shadow-leaf-700/5 border border-leaf-100 p-8 sm:p-10 max-w-2xl">
    <h1 class="text-3xl font-bold text-leaf-900 mb-2">{{ $title }}</h1>
    <p class="text-xs text-leaf-500 mb-6">Atualizado em {{ now()->format('d/m/Y') }}</p>

    <div class="prose prose-sm max-w-none text-leaf-700 space-y-4 [&_h2]:text-leaf-900 [&_h2]:font-bold [&_h2]:text-base [&_h2]:mt-6 [&_h2]:mb-2 [&_p]:leading-relaxed">
        {!! $body !!}
    </div>

    <div class="mt-8 pt-6 border-t border-leaf-100 text-sm">
        <a href="/" class="text-leaf-700 font-semibold hover:underline">← Voltar ao site</a>
    </div>
</article>
@endsection
