<?php

namespace App\Http\Requests\Customers;

use App\Support\NameNormalizer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', \App\Models\Customer::class) ?? false;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('nome')) {
            $this->merge(['nome' => NameNormalizer::normalize($this->input('nome'))]);
        }
    }

    public function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'min:2', 'max:150'],
            'telefone' => ['nullable', 'string', 'max:30'],
            'cpf_cnpj' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('customers', 'cpf_cnpj')
                    ->where(fn ($q) => $q->where('farm_id', $this->user()->farm_id)),
            ],
            'observacoes' => ['nullable', 'string', 'max:2000'],
            'saldo_cafe_kg' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
