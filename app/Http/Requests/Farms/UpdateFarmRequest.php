<?php

namespace App\Http\Requests\Farms;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFarmRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->user()->farm) ?? false;
    }

    public function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'min:2', 'max:150'],
            'telefone' => ['nullable', 'string', 'max:30'],
            'cidade' => ['nullable', 'string', 'max:120'],
            'estado' => ['nullable', 'string', 'size:2'],
        ];
    }
}
