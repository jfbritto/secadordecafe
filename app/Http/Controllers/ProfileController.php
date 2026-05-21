<?php

namespace App\Http\Controllers;

use App\Http\Requests\Profile\UpdatePasswordRequest;
use App\Http\Requests\Profile\UpdateProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(): View
    {
        return view('profile.edit', ['user' => auth()->user()]);
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validated();

        // Se trocou o e-mail, marca como não-verificado pra forçar reverificação futura.
        if ($data['email'] !== $user->email) {
            $data['email_verified_at'] = null;
        }

        $user->update($data);

        return redirect()->route('perfil.edit')
            ->with('flash', 'Seus dados foram atualizados.');
    }

    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->update(['password' => $request->validated('password')]);
        // O cast 'hashed' no model cuida do bcrypt automaticamente.

        return redirect()->route('perfil.edit')
            ->with('flash', 'Senha alterada com sucesso.');
    }
}
