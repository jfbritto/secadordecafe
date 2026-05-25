<?php

namespace App\Http\Requests\Colheitas;

use App\Models\Area;
use App\Models\Movement;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreColheitaRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Reaproveita permissão de criar Movement do tipo "entrada"
        return $this->user()?->can('create', [Movement::class, Movement::TIPO_COLHEITA]) ?? false;
    }

    public function rules(): array
    {
        return [
            'area_id' => [
                'required',
                Rule::exists('areas', 'id')
                    ->where(fn ($q) => $q->where('farm_id', $this->user()->farm_id)->where('ativo', true)),
            ],
            'quantidade_kg' => ['required', 'numeric', 'gt:0'],
            'observacao' => ['nullable', 'string', 'max:500'],
            'occurred_at' => ['nullable', 'date'],
        ];
    }
}
