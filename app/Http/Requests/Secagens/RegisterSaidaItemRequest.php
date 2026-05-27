<?php

namespace App\Http\Requests\Secagens;

use App\Models\SecagemItem;
use Illuminate\Contracts\Validation\Validator;
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

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            if ($v->errors()->has('quantidade_seca_kg')) {
                return;
            }

            $item = $this->route('item');
            if (! $item instanceof SecagemItem) {
                return;
            }

            $seca = (float) $this->input('quantidade_seca_kg');
            $recebida = (float) $item->quantidade_recebida_kg;

            // Trava física: secar só remove água/casca, então o seco nunca pode
            // ser maior que o côco recebido (rendimento máximo = 100%).
            if ($seca > $recebida) {
                $v->errors()->add(
                    'quantidade_seca_kg',
                    'O café seco (' . number_format($seca, 2, ',', '.') . ' kg) não pode ser maior que o café côco recebido ('
                    . number_format($recebida, 2, ',', '.') . ' kg). Confira os valores.'
                );
            }
        });
    }
}
