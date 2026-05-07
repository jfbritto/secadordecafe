<?php

namespace App\Http\Requests\Secagens;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSecagemItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('secagem')) ?? false;
    }

    public function rules(): array
    {
        return [
            'customer_id' => [
                'required',
                Rule::exists('customers', 'id')->where('farm_id', $this->user()->farm_id),
            ],
            'quantidade_recebida_kg' => ['required', 'numeric', 'gt:0'],
            'quantidade_seca_kg' => ['required', 'numeric', 'gt:0'],
            'comissao_percentual' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];
    }
}
