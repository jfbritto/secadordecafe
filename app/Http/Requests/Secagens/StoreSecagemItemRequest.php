<?php

namespace App\Http\Requests\Secagens;

use App\Models\Area;
use App\Models\Customer;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Request pra adicionar item à secagem.
 *
 * Polimórfico: o lote pode ser de um Customer (cliente externo trouxe café côco)
 * ou de uma Area (café próprio que veio da colheita). Validação garante que o
 * owner tem saldo de côco suficiente.
 *
 * Saída (quantidade_seca_kg + comissão) NÃO entra aqui — vai num PATCH separado
 * quando o café sai do secador.
 */
class StoreSecagemItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('secagem')) ?? false;
    }

    public function rules(): array
    {
        $secagem = $this->route('secagem');
        $secagemId = $secagem?->id ?? 0;

        return [
            'origin_type' => ['required', 'in:cliente,area'],
            'origin_id' => [
                'required',
                'integer',
                Rule::unique('secagem_items', 'origin_id')
                    ->where(fn ($q) => $q->where('secagem_id', $secagemId)
                        ->where('origin_type', $this->resolvedOriginClass())),
            ],
            'quantidade_recebida_kg' => ['required', 'numeric', 'gt:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'origin_id.unique' => 'Essa origem já tem um item nesta secagem. Remova o item existente se precisar ajustar.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            if ($v->errors()->hasAny(['origin_type', 'origin_id', 'quantidade_recebida_kg'])) {
                return;
            }

            $originClass = $this->resolvedOriginClass();
            $origin = $originClass::query()
                ->where('id', $this->input('origin_id'))
                ->where('farm_id', $this->user()->farm_id)
                ->first();

            if (! $origin) {
                $v->errors()->add('origin_id', 'Origem não encontrada ou fora da fazenda.');
                return;
            }

            $recebida = (float) $this->input('quantidade_recebida_kg');

            // Cliente tem saldo próprio; Area aponta pro estoque da Farm.
            if ($origin instanceof Customer) {
                $saldo = (float) $origin->saldo_coco_kg;
                $label = $origin->nome;
            } else {
                $farm = \App\Models\Farm::query()->whereKey($this->user()->farm_id)->first();
                $saldo = (float) ($farm->saldo_coco_kg ?? 0);
                $label = "estoque próprio (área {$origin->nome})";
            }

            if ($recebida > $saldo) {
                $v->errors()->add(
                    'quantidade_recebida_kg',
                    "Saldo de café côco insuficiente. {$label} tem apenas " . number_format($saldo, 2, ',', '.') . ' kg disponível.'
                );
            }
        });
    }

    public function resolvedOriginClass(): string
    {
        return match ($this->input('origin_type')) {
            'cliente' => Customer::class,
            'area'    => Area::class,
            default   => Customer::class,
        };
    }
}
