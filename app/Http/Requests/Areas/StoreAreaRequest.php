<?php

namespace App\Http\Requests\Areas;

use App\Models\Area;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAreaRequest extends FormRequest
{
    public function authorize(): bool
    {
        $area = $this->route('area');
        if ($area instanceof Area) {
            return $this->user()?->can('update', $area) ?? false;
        }
        return $this->user()?->can('create', Area::class) ?? false;
    }

    public function rules(): array
    {
        $farmId = $this->user()->farm_id;
        $area = $this->route('area');
        $ignoreId = $area instanceof Area ? $area->id : null;

        return [
            'nome' => [
                'required', 'string', 'min:2', 'max:120',
                Rule::unique('areas', 'nome')->where(fn ($q) => $q->where('farm_id', $farmId))->ignore($ignoreId),
            ],
            'observacoes' => ['nullable', 'string', 'max:2000'],
            // Lat/Lng vêm do botão "Capturar localização atual" (navigator.geolocation).
            // Aceitamos string vazia (form vazio) e validamos como decimal opcional.
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'ativo' => ['nullable', 'boolean'],
        ];
    }

    public function prepareForValidation(): void
    {
        $this->merge([
            'ativo' => $this->boolean('ativo', true),
            // String vazia do form vira null
            'latitude' => $this->input('latitude') === '' ? null : $this->input('latitude'),
            'longitude' => $this->input('longitude') === '' ? null : $this->input('longitude'),
        ]);
    }
}
