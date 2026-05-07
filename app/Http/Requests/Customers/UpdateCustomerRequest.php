<?php

namespace App\Http\Requests\Customers;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('cliente')) ?? false;
    }

    public function rules(): array
    {
        $customerId = $this->route('cliente')->id;

        return [
            'nome' => ['required', 'string', 'min:2', 'max:150'],
            'telefone' => ['nullable', 'string', 'max:30'],
            'cpf_cnpj' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('customers', 'cpf_cnpj')
                    ->ignore($customerId)
                    ->where(fn ($q) => $q->where('farm_id', $this->user()->farm_id)),
            ],
            'observacoes' => ['nullable', 'string', 'max:2000'],
            'saldo_cafe_kg' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
