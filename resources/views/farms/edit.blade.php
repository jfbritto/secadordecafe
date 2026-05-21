@extends('layouts.app')

@section('title', 'Configurações da fazenda')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-leaf-900">Configurações da fazenda</h1>
        <p class="text-sm text-leaf-500 mt-1">Dados que aparecem no dashboard, em e-mails e nos relatórios.</p>
    </div>

    <form method="POST" action="{{ route('fazenda.update') }}" class="bg-white rounded-2xl border border-leaf-100 shadow-sm p-6 sm:p-8">
        @csrf @method('PUT')

        <div class="border-b border-leaf-100 pb-5 mb-6">
            <h2 class="text-base font-bold text-leaf-900">Identificação</h2>
            <p class="text-sm text-leaf-500 mt-0.5">Como sua fazenda é exibida no sistema.</p>
        </div>

        <div class="space-y-5 mb-6">
            <div>
                <label for="nome" class="block text-sm font-bold text-leaf-900 mb-2">
                    Nome da fazenda <span class="text-rose-500">*</span>
                </label>
                <input id="nome" type="text" name="nome" value="{{ old('nome', $farm->nome) }}" required maxlength="150" autofocus
                       placeholder="ex: Fazenda Paraíso"
                       class="w-full px-4 py-3 text-base rounded-lg border border-leaf-200 placeholder-leaf-300 focus:border-leaf-500 focus:ring-4 focus:ring-leaf-500/15 outline-none transition">
                <p class="mt-1.5 text-sm text-leaf-500">Aparece no topo do sistema e nos relatórios em PDF.</p>
                @error('nome')<p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="telefone" class="block text-sm font-bold text-leaf-900 mb-2">Telefone</label>
                <input id="telefone" type="tel" name="telefone" maxlength="20" inputmode="numeric"
                       data-mask="phone"
                       value="{{ old('telefone', $farm->telefone) }}"
                       placeholder="(00) 00000-0000"
                       class="w-full px-4 py-3 text-base rounded-lg border border-leaf-200 placeholder-leaf-300 focus:border-leaf-500 focus:ring-4 focus:ring-leaf-500/15 outline-none transition">
                <p class="mt-1.5 text-sm text-leaf-500">Telefone de contato. Opcional.</p>
            </div>
        </div>

        <div class="border-b border-leaf-100 pb-5 mb-6">
            <h2 class="text-base font-bold text-leaf-900">Localização</h2>
            <p class="text-sm text-leaf-500 mt-0.5">Cidade e estado da propriedade.</p>
        </div>

        <div class="grid grid-cols-3 gap-4 mb-6">
            <div class="col-span-2">
                <label for="cidade" class="block text-sm font-bold text-leaf-900 mb-2">Cidade</label>
                <input id="cidade" type="text" name="cidade" value="{{ old('cidade', $farm->cidade) }}" maxlength="120"
                       placeholder="ex: Manhuaçu"
                       class="w-full px-4 py-3 text-base rounded-lg border border-leaf-200 placeholder-leaf-300 focus:border-leaf-500 focus:ring-4 focus:ring-leaf-500/15 outline-none transition">
            </div>
            <div>
                <label for="estado" class="block text-sm font-bold text-leaf-900 mb-2">UF</label>
                <input id="estado" type="text" name="estado" maxlength="2" data-mask="uf"
                       value="{{ old('estado', $farm->estado) }}"
                       placeholder="MG"
                       class="w-full px-4 py-3 text-base rounded-lg border border-leaf-200 placeholder-leaf-300 focus:border-leaf-500 focus:ring-4 focus:ring-leaf-500/15 outline-none transition uppercase">
                <p class="mt-1.5 text-xs text-leaf-500">2 letras</p>
            </div>
        </div>

        <div class="flex flex-col-reverse sm:flex-row sm:items-center gap-3 pt-6 border-t border-leaf-100">
            <button type="submit" class="px-6 py-3 text-base font-bold text-white bg-leaf-700 hover:bg-leaf-800 rounded-lg transition shadow-sm w-full sm:w-auto">
                Salvar alterações
            </button>
        </div>
    </form>

    <div class="mt-5 bg-white rounded-2xl border border-leaf-100 p-6 text-sm">
        <h3 class="text-sm font-bold text-leaf-900 uppercase tracking-wider mb-3">Informações do sistema</h3>
        <dl class="grid sm:grid-cols-3 gap-4 text-sm">
            <div>
                <dt class="text-xs text-leaf-500 font-semibold uppercase">Status</dt>
                @php $cls = match($farm->status){'active'=>'bg-emerald-100 text-emerald-700','blocked'=>'bg-rose-100 text-rose-700','past_due'=>'bg-amber-100 text-amber-700', default=>'bg-amber-100 text-amber-700'}; @endphp
                <dd class="mt-1"><span class="inline-block px-2 py-0.5 rounded-full text-xs font-semibold {{ $cls }}">{{ strtoupper($farm->status) }}</span></dd>
            </div>
            <div>
                <dt class="text-xs text-leaf-500 font-semibold uppercase">Slug</dt>
                <dd class="text-leaf-700 font-mono text-xs mt-1">{{ $farm->slug }}</dd>
            </div>
            <div>
                <dt class="text-xs text-leaf-500 font-semibold uppercase">Criada em</dt>
                <dd class="text-leaf-700 mt-1">{{ $farm->created_at->format('d/m/Y') }}</dd>
            </div>
        </dl>
    </div>
</div>
@endsection
