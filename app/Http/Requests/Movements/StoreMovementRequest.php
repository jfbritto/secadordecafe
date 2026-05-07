<?php

namespace App\Http\Requests\Movements;

use App\Models\Movement;
use Illuminate\Foundation\Http\FormRequest;

class StoreMovementRequest extends FormRequest
{
    public function authorize(): bool
    {
        $tipo = (string) $this->input('tipo');
        return $this->user()?->can('create', [Movement::class, $tipo]) ?? false;
    }

    public function rules(): array
    {
        return [
            'tipo' => ['required', 'in:entrada,saida,ajuste'],
            'direcao' => ['nullable', 'in:+,-'],
            'quantidade' => ['required', 'numeric', 'gt:0'],
            'observacao' => ['nullable', 'string', 'max:500'],
            'occurred_at' => ['nullable', 'date'],
        ];
    }
}
