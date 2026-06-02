<?php

namespace App\Http\Requests\Compras;

use App\Models\Expense;
use App\Models\Movement;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCompraCafeRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Compra gera Expense — reusa a regra "quem pode criar despesa pode comprar café".
        return $this->user()?->can('create', Expense::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'data' => ['required', 'date', 'before_or_equal:today'],
            'produto' => ['required', Rule::in([Movement::PRODUTO_COCO, Movement::PRODUTO_SECO])],
            'quantidade_kg' => ['required', 'numeric', 'gt:0', 'max:9999999.99'],
            'valor_unitario' => ['nullable', 'numeric', 'min:0', 'max:9999999.99'],
            'valor_total' => ['required', 'numeric', 'min:0.01', 'max:9999999.99'],
            'fornecedor' => ['nullable', 'string', 'max:120'],
            'observacoes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'data.before_or_equal' => 'A data da compra não pode ser futura.',
            'produto.in' => 'Selecione se é café côco ou seco.',
            'quantidade_kg.gt' => 'Informe uma quantidade maior que zero.',
            'valor_total.min' => 'O valor total da compra deve ser maior que zero.',
        ];
    }

    public function prepareForValidation(): void
    {
        if (! $this->filled('valor_total') && $this->filled('quantidade_kg') && $this->filled('valor_unitario')) {
            $this->merge([
                'valor_total' => round((float) $this->input('quantidade_kg') * (float) $this->input('valor_unitario'), 2),
            ]);
        }
    }
}
