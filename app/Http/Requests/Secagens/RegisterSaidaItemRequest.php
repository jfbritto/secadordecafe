<?php

namespace App\Http\Requests\Secagens;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Registra a saída do café seco depois do ciclo do secador.
 * Separado da entrada porque o ciclo dura 10-15h e o sogro lança em momentos diferentes.
 *
 * Comissão só faz sentido se a origem é Customer — pra Area o sistema ignora.
 */
class RegisterSaidaItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('secagem')) ?? false;
    }

    public function rules(): array
    {
        return [
            'quantidade_seca_kg' => ['required', 'numeric', 'gt:0'],
            'comissao_percentual' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];
    }
}
