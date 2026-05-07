<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', User::class);

        $farmId = $request->user()->farm_id;
        $users = User::query()
            ->where('farm_id', $farmId)
            ->orderBy('name')
            ->paginate(20);

        $pendingInvitations = Invitation::query()
            ->where('farm_id', $farmId)
            ->pending()
            ->orderByDesc('created_at')
            ->get();

        return view('users.index', compact('users', 'pendingInvitations'));
    }

    public function updateRole(Request $request, User $usuario): RedirectResponse
    {
        $this->authorize('updateRole', $usuario);

        $data = Validator::make($request->all(), [
            'role' => ['required', 'in:admin,operador,financeiro,visualizador'],
        ])->validate();

        $newRole = $data['role'];

        if ($this->isLastAdminBeingDemoted($usuario, $newRole)) {
            return redirect()->route('usuarios.index')
                ->with('error', 'Não é possível remover a última administradora da fazenda.');
        }

        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($usuario->farm_id);
        $usuario->syncRoles([$newRole]);

        return redirect()->route('usuarios.index')
            ->with('flash', '<strong>' . e($usuario->name) . '</strong> agora é <strong>' . ucfirst($newRole) . '</strong>.');
    }

    public function destroy(User $usuario): RedirectResponse
    {
        $this->authorize('delete', $usuario);

        if ($this->isLastAdminBeingDemoted($usuario, null)) {
            return redirect()->route('usuarios.index')
                ->with('error', 'Não é possível remover a última administradora.');
        }

        $nome = $usuario->name;
        $usuario->delete();
        return redirect()->route('usuarios.index')
            ->with('flash', '<strong>' . e($nome) . '</strong> removido(a) da fazenda.');
    }

    private function isLastAdminBeingDemoted(User $target, ?string $newRole): bool
    {
        if (! $target->hasRole('admin')) {
            return false;
        }
        if ($newRole === 'admin') {
            return false;
        }
        $admins = User::query()
            ->where('farm_id', $target->farm_id)
            ->whereHas('roles', fn ($q) => $q->where('name', 'admin'))
            ->count();
        return $admins <= 1;
    }
}
