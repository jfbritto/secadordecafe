<?php

namespace App\Http\Requests\Expenses;

use App\Models\Expense;
use Illuminate\Foundation\Http\FormRequest;

class StoreExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Expense::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'data' => ['required', 'date'],
            'descricao' => ['required', 'string', 'min:2', 'max:200'],
            'categoria' => ['required', 'in:' . implode(',', array_keys(Expense::CATEGORIAS))],
            'unidade' => ['nullable', 'string', 'max:20'],
            'quantidade' => ['nullable', 'numeric', 'gt:0'],
            'valor_unitario' => ['nullable', 'numeric', 'min:0'],
            'valor_total' => ['required', 'numeric', 'min:0.01'],
            'observacoes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function prepareForValidation(): void
    {
        // Se valor_total não veio, derivar
        if (! $this->filled('valor_total') && $this->filled('quantidade') && $this->filled('valor_unitario')) {
            $this->merge([
                'valor_total' => round((float) $this->input('quantidade') * (float) $this->input('valor_unitario'), 2),
            ]);
        }
    }
}
