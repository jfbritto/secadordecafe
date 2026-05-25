<?php

namespace App\Actions\Auth;

use App\DTOs\RegisterFarmData;
use App\Events\FarmRegistered;
use App\Models\Farm;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;

class RegisterFarmAction
{
    public function execute(RegisterFarmData $data): User
    {
        // Enquanto a estrutura de planos/cobrança não está definida, todo registro
        // entra como parceiro: acesso completo, sem expiração, sem cobrança.
        $user = DB::transaction(function () use ($data) {
            $farm = Farm::create([
                'nome' => $data->farmName,
                'slug' => $this->uniqueSlug($data->farmName),
                'status' => Farm::STATUS_PARTNER,
                'trial_ends_at' => null,
            ]);

            $user = User::create([
                'farm_id' => $farm->id,
                'name' => $data->userName,
                'email' => $data->email,
                'password' => $data->password,
                'is_root' => false,
            ]);

            Subscription::create([
                'farm_id' => $farm->id,
                'status' => Subscription::STATUS_PARTNER,
                'trial_ends_at' => null,
            ]);

            app(PermissionRegistrar::class)->setPermissionsTeamId($farm->id);
            $user->assignRole('admin');

            return $user;
        });

        FarmRegistered::dispatch($user->fresh('farm'));

        return $user;
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'fazenda';
        $slug = $base;
        $i = 2;
        while (Farm::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i;
            $i++;
        }
        return $slug;
    }
}
