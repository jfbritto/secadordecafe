<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RootUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = 'root@secadordecafe.test';
        if (User::where('email', $email)->exists()) {
            return;
        }

        $password = app()->environment('local', 'testing') ? 'root12345' : Str::password(16);

        User::create([
            'farm_id' => null,
            'name' => 'Root',
            'email' => $email,
            'password' => Hash::make($password),
            'is_root' => true,
            'email_verified_at' => now(),
        ]);
        // Root bypassa policies via Gate::before + is_root; sem role Spatie necessária.

        $this->command?->info("Usuário ROOT criado: {$email} / senha: {$password}");
    }
}
