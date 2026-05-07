<?php

namespace App\Http\Requests\ExpenseCategories;

use App\Models\ExpenseCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreExpenseCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        $cat = $this->route('categoria');
        if ($cat instanceof ExpenseCategory) {
            return $this->user()?->can('update', $cat) ?? false;
        }
        return $this->user()?->can('create', ExpenseCategory::class) ?? false;
    }

    public function rules(): array
    {
        $farmId = $this->user()->farm_id;
        $cat = $this->route('categoria');
        $ignoreId = $cat instanceof ExpenseCategory ? $cat->id : null;

        return [
            'nome' => [
                'required', 'string', 'min:2', 'max:80',
                Rule::unique('expense_categories', 'nome')
                    ->where(fn ($q) => $q->where('farm_id', $farmId))
                    ->ignore($ignoreId),
            ],
            'observacoes' => ['nullable', 'string', 'max:2000'],
            'ativo' => ['nullable', 'boolean'],
        ];
    }

    public function prepareForValidation(): void
    {
        $this->merge(['ativo' => $this->boolean('ativo', true)]);
    }
}
