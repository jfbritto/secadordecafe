<?php

namespace App\Http\Requests\Secagens;

use App\Models\Customer;
use App\Models\SecagemItem;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'customer_id' => [
                'required',
                Rule::exists('customers', 'id')->where('farm_id', $this->user()->farm_id),
                // Impede o mesmo cliente aparecer duas vezes na mesma secagem
                Rule::unique('secagem_items', 'customer_id')
                    ->where(fn ($q) => $q->where('secagem_id', $secagemId)),
            ],
            'quantidade_recebida_kg' => ['required', 'numeric', 'gt:0'],
            'quantidade_seca_kg' => ['required', 'numeric', 'gt:0'],
            'comissao_percentual' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'customer_id.unique' => 'Este cliente já está nesta secagem. Remova o item existente e adicione de novo se precisar ajustar os valores.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            if ($v->errors()->hasAny(['customer_id', 'quantidade_recebida_kg'])) {
                return;
            }

            $customer = Customer::find($this->input('customer_id'));
            if (! $customer) {
                return;
            }

            $recebida = (float) $this->input('quantidade_recebida_kg');
            $saldo = (float) $customer->saldo_cafe_kg;

            if ($recebida > $saldo) {
                $v->errors()->add(
                    'quantidade_recebida_kg',
                    "Saldo insuficiente. {$customer->nome} tem apenas " . number_format($saldo, 3, ',', '.') . ' kg disponível.'
                );
            }
        });
    }
}
