<?php

namespace App\Http\Requests\Secagens;

use App\Models\SecagemItem;
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
        $secagem = $this->route('secagem');
        $secagemId = $secagem?->id ?? 0;

        return [
            'customer_id' => [
                'required',
                Rule::exists('customers', 'id')->where('farm_id', $this->user()->farm_id),
                // Impede o mesmo cliente aparecer duas vezes na mesma secagem
                Rule::unique('secagem_items', 'customer_id')
                    ->where(fn ($q) => $q->where('secagem_id', $secagemId)),
            ],
            'quantidade_recebida_kg' => ['required', 'numeric', 'gt:0'],
            'quantidade_seca_kg' => ['required', 'numeric', 'gt:0'],
            'comissao_percentual' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'customer_id.unique' => 'Este cliente já está nesta secagem. Remova o item existente e adicione de novo se precisar ajustar os valores.',
        ];
    }
}
