<?php

namespace App\Http\Requests\Dryers;

use App\Models\Dryer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDryerRequest extends FormRequest
{
    public function authorize(): bool
    {
        $dryer = $this->route('secador');
        if ($dryer instanceof Dryer) {
            return $this->user()?->can('update', $dryer) ?? false;
        }
        return $this->user()?->can('create', Dryer::class) ?? false;
    }

    public function rules(): array
    {
        $farmId = $this->user()->farm_id;
        $dryer = $this->route('secador');
        $ignoreId = $dryer instanceof Dryer ? $dryer->id : null;

        return [
            'nome' => [
                'required', 'string', 'min:2', 'max:80',
                Rule::unique('dryers', 'nome')->where(fn ($q) => $q->where('farm_id', $farmId))->ignore($ignoreId),
            ],
            'capacidade_kg' => ['nullable', 'numeric', 'min:0'],
            'modelo' => ['nullable', 'string', 'max:80'],
            'observacoes' => ['nullable', 'string', 'max:2000'],
            'ativo' => ['nullable', 'boolean'],
        ];
    }

    public function prepareForValidation(): void
    {
        $this->merge(['ativo' => $this->boolean('ativo', true)]);
    }
}
