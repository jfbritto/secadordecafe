<?php

namespace App\Http\Requests\Expenses;

use App\Models\Expense;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Expense::class) ?? false;
    }

    public function rules(): array
    {
        $unidade = $this->input('unidade');
        $isDiscrete = Expense::unidadeEhDiscreta($unidade);

        return [
            'data' => ['required', 'date'],
            'descricao' => ['required', 'string', 'min:2', 'max:200'],
            'expense_category_id' => [
                'required',
                Rule::exists('expense_categories', 'id')
                    ->where(fn ($q) => $q->where('farm_id', $this->user()->farm_id)->where('ativo', true)),
            ],
            'unidade' => ['nullable', 'string', 'max:20'],
            'quantidade' => $isDiscrete
                ? ['nullable', 'integer', 'gt:0']
                : ['nullable', 'numeric', 'gt:0'],
            'valor_unitario' => ['nullable', 'numeric', 'min:0'],
            'valor_total' => ['required', 'numeric', 'min:0.01'],
            'observacoes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        $unidade = $this->input('unidade');
        $msgs = [
            'expense_category_id.required' => 'Selecione uma categoria.',
            'expense_category_id.exists' => 'Categoria inválida.',
        ];
        if (Expense::unidadeEhDiscreta($unidade)) {
            $msgs['quantidade.integer'] = "Para a unidade \"{$unidade}\" a quantidade deve ser um número inteiro.";
        }
        return $msgs;
    }

    public function prepareForValidation(): void
    {
        if (! $this->filled('valor_total') && $this->filled('quantidade') && $this->filled('valor_unitario')) {
            $this->merge([
                'valor_total' => round((float) $this->input('quantidade') * (float) $this->input('valor_unitario'), 2),
            ]);
        }
    }
}
