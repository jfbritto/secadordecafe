<?php

namespace App\Http\Requests\Secagens;

use App\Models\Secagem;
use Illuminate\Foundation\Http\FormRequest;

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
            'secador' => ['required', 'string', 'max:80'],
            'observacoes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
