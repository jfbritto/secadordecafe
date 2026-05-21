@csrf

@php $A = $area ?? null; @endphp

<div class="border-b border-coffee-100 pb-5 mb-6">
    <h2 class="text-base font-bold text-coffee-900">Identificação da área</h2>
    <p class="text-sm text-coffee-500 mt-0.5">Como esta área (talhão/lote) será exibida nas listas e nas secagens.</p>
</div>

<div class="space-y-5 mb-6">
    <div>
        <label for="nome" class="block text-sm font-bold text-coffee-900 mb-2">
            Nome <span class="text-rose-500">*</span>
        </label>
        <input id="nome" type="text" name="nome" required maxlength="120" autofocus
               value="{{ old('nome', $A?->nome) }}"
               placeholder="ex: Talhão Norte, Cafezal do Morro, Quadra A"
               class="w-full px-4 py-3 text-base rounded-lg border border-coffee-200 placeholder-coffee-300 focus:border-coffee-500 focus:ring-4 focus:ring-coffee-500/15 outline-none transition">
        <p class="mt-1.5 text-sm text-coffee-500">Nome único — o que a equipe usa pra se referir a essa parte da roça.</p>
        @error('nome')<p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="observacoes" class="block text-sm font-bold text-coffee-900 mb-2">Observações</label>
        <textarea id="observacoes" name="observacoes" rows="3"
                  placeholder="Ex: variedade plantada, idade do cafezal, particularidades do terreno…"
                  class="w-full px-4 py-3 text-base rounded-lg border border-coffee-200 placeholder-coffee-300 focus:border-coffee-500 focus:ring-4 focus:ring-coffee-500/15 outline-none transition">{{ old('observacoes', $A?->observacoes) }}</textarea>
    </div>
</div>

<div class="border-b border-coffee-100 pb-5 mb-6">
    <h2 class="text-base font-bold text-coffee-900">Localização</h2>
    <p class="text-sm text-coffee-500 mt-0.5">Opcional. Use o celular dentro da área pra capturar — depois fica fácil achar no mapa.</p>
</div>

<div class="mb-6" x-data="{
    lat: @js(old('latitude', $A?->latitude)),
    lng: @js(old('longitude', $A?->longitude)),
    status: '',
    loading: false,
    capturar() {
        this.status = '';
        if (!navigator.geolocation) {
            this.status = 'Seu navegador não suporta geolocalização.';
            return;
        }
        this.loading = true;
        navigator.geolocation.getCurrentPosition(
            (pos) => {
                this.lat = pos.coords.latitude.toFixed(7);
                this.lng = pos.coords.longitude.toFixed(7);
                this.status = 'Localização capturada (precisão ~' + Math.round(pos.coords.accuracy) + 'm).';
                this.loading = false;
            },
            (err) => {
                this.status = 'Não foi possível capturar: ' + err.message;
                this.loading = false;
            },
            { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
        );
    },
    limpar() {
        this.lat = '';
        this.lng = '';
        this.status = 'Localização removida.';
    }
}">
    <div class="grid sm:grid-cols-2 gap-5 mb-3">
        <div>
            <label for="latitude" class="block text-sm font-bold text-coffee-900 mb-2">Latitude</label>
            <input id="latitude" type="text" name="latitude" x-model="lat" readonly
                   placeholder="—"
                   class="w-full px-4 py-3 text-base rounded-lg border border-coffee-200 bg-coffee-50/50 text-coffee-700 outline-none">
            @error('latitude')<p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="longitude" class="block text-sm font-bold text-coffee-900 mb-2">Longitude</label>
            <input id="longitude" type="text" name="longitude" x-model="lng" readonly
                   placeholder="—"
                   class="w-full px-4 py-3 text-base rounded-lg border border-coffee-200 bg-coffee-50/50 text-coffee-700 outline-none">
            @error('longitude')<p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="flex flex-wrap gap-2 items-center">
        <button type="button" @click="capturar()" :disabled="loading"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-coffee-700 hover:bg-coffee-800 disabled:opacity-50 disabled:cursor-not-allowed rounded-lg transition shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" x-show="!loading"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" x-show="loading" x-cloak><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            <span x-text="loading ? 'Capturando…' : 'Capturar localização atual'"></span>
        </button>
        <button type="button" @click="limpar()" x-show="lat && lng" x-cloak
                class="text-sm font-semibold text-coffee-600 hover:text-coffee-900 hover:underline">
            Limpar
        </button>
        <a x-show="lat && lng" x-cloak :href="'https://www.google.com/maps?q=' + lat + ',' + lng" target="_blank" rel="noopener"
           class="inline-flex items-center gap-1 text-sm font-semibold text-coffee-700 hover:underline">
            Ver no mapa
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
        </a>
    </div>

    <p class="mt-2 text-sm text-coffee-500" x-show="status" x-text="status" x-cloak></p>
    <p class="mt-2 text-sm text-coffee-500" x-show="!status">
        O navegador vai pedir permissão de localização. Funciona melhor quando você está fisicamente dentro da área.
    </p>
</div>

<div class="border-b border-coffee-100 pb-5 mb-6">
    <h2 class="text-base font-bold text-coffee-900">Disponibilidade</h2>
    <p class="text-sm text-coffee-500 mt-0.5">Controle se esta área aparece no menu ao criar uma nova secagem.</p>
</div>

<div class="mb-6">
    <label class="flex items-start gap-3 p-4 rounded-lg border-2 border-coffee-200 cursor-pointer hover:bg-coffee-50/50 transition has-[:checked]:border-coffee-500 has-[:checked]:bg-coffee-50">
        <input type="hidden" name="ativo" value="0">
        <input type="checkbox" name="ativo" value="1" {{ old('ativo', $A?->ativo ?? true) ? 'checked' : '' }}
               class="mt-0.5 w-5 h-5 rounded border-coffee-300 text-coffee-700 focus:ring-coffee-500">
        <div>
            <span class="block text-sm font-bold text-coffee-900">Área ativa</span>
            <span class="block text-sm text-coffee-500 mt-0.5">
                Marque para que esta área apareça como opção ao criar uma nova secagem. Desmarque se foi desativada (mas o histórico continua aqui).
            </span>
        </div>
    </label>
</div>
