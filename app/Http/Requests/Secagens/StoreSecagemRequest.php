<?php

namespace App\Http\Requests\Secagens;

use App\Models\Secagem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSecagemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Secagem::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'data' => ['required', 'date'],
            'apelido' => ['nullable', 'string', 'max:80'],
            'dryer_id' => [
                'required',
                Rule::exists('dryers', 'id')
                    ->where(fn ($q) => $q->where('farm_id', $this->user()->farm_id)->where('ativo', true)),
            ],
            'observacoes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'dryer_id.required' => 'Selecione um secador.',
            'dryer_id.exists' => 'Secador inválido.',
        ];
    }
}
