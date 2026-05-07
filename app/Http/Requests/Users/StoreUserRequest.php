<?php

namespace App\Http\Requests\Users;

use App\Models\User;
use App\Support\PermissionsMatrix;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', User::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:180', 'unique:users,email'],
            'role' => ['required', Rule::in(array_keys(PermissionsMatrix::ROLES))],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ];
    }

    public function messages(): array
    {
        return [
            'role.in' => 'Permissão inválida.',
            'email.unique' => 'Este e-mail já está em uso por outro usuário.',
        ];
    }
}
