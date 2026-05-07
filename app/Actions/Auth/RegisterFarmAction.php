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
        $trialDays = (int) config('farm.trial_days', 14);

        $user = DB::transaction(function () use ($data, $trialDays) {
            $farm = Farm::create([
                'nome' => $data->farmName,
                'slug' => $this->uniqueSlug($data->farmName),
                'status' => Farm::STATUS_TRIAL,
                'trial_ends_at' => now()->addDays($trialDays),
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
                'status' => Subscription::STATUS_TRIAL,
                'trial_ends_at' => $farm->trial_ends_at,
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
