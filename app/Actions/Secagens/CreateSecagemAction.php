<?php

namespace App\Actions\Secagens;

use App\Models\Secagem;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreateSecagemAction
{
    public function execute(User $user, array $data): Secagem
    {
        return DB::transaction(function () use ($user, $data) {
            $next = (int) Secagem::query()
                ->where('farm_id', $user->farm_id)
                ->lockForUpdate()
                ->max('numero') + 1;

            return Secagem::create([
                'farm_id' => $user->farm_id,
                'user_id' => $user->id,
                'numero' => $next,
                'data' => $data['data'],
                'secador' => $data['secador'],
                'observacoes' => $data['observacoes'] ?? null,
                'status' => Secagem::STATUS_RASCUNHO,
            ]);
        });
    }
}
