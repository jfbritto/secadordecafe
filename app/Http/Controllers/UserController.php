<?php

namespace App\Http\Controllers;

use App\Http\Requests\Users\StoreUserRequest;
use App\Models\Invitation;
use App\Models\User;
use App\Support\PermissionsMatrix;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Spatie\Permission\PermissionRegistrar;

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

    public function create(): View
    {
        $this->authorize('create', User::class);
        return view('users.create');
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $request) {
            $user = User::create([
                'farm_id' => $request->user()->farm_id,
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'is_root' => false,
                'email_verified_at' => now(),
            ]);
            app(PermissionRegistrar::class)->setPermissionsTeamId($request->user()->farm_id);
            $user->assignRole($data['role']);
        });

        $roleLabel = PermissionsMatrix::ROLES[$data['role']]['label'] ?? ucfirst($data['role']);
        return redirect()->route('usuarios.index')
            ->with('flash', '<strong>' . e($data['name']) . '</strong> cadastrado(a) como <strong>' . $roleLabel . '</strong>.');
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

        app(PermissionRegistrar::class)->setPermissionsTeamId($usuario->farm_id);
        $usuario->syncRoles([$newRole]);

        $roleLabel = PermissionsMatrix::ROLES[$newRole]['label'] ?? ucfirst($newRole);
        return redirect()->route('usuarios.index')
            ->with('flash', '<strong>' . e($usuario->name) . '</strong> agora é <strong>' . $roleLabel . '</strong>.');
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
