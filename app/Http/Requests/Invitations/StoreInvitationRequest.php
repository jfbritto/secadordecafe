<?php

namespace App\Http\Requests\Invitations;

use App\Models\Invitation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreInvitationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Invitation::class) ?? false;
    }

    public function rules(): array
    {
        $farmId = $this->user()->farm_id;

        return [
            'email' => [
                'required', 'email', 'max:180',
                Rule::unique('users', 'email')->where(fn ($q) => $q->where('farm_id', $farmId)),
            ],
            'role' => ['required', 'in:admin,operador,financeiro,visualizador'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($v) {
            $email = strtolower((string) $this->input('email'));
            $hasPending = Invitation::query()
                ->where('farm_id', $this->user()->farm_id)
                ->whereRaw('LOWER(email) = ?', [$email])
                ->whereNull('accepted_at')
                ->where('expires_at', '>', now())
                ->exists();
            if ($hasPending) {
                $v->errors()->add('email', 'Já existe um convite pendente para este e-mail.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'Este e-mail já pertence a um usuário da fazenda.',
        ];
    }
}
