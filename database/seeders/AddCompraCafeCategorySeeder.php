<?php

namespace Database\Seeders;

use App\Models\ExpenseCategory;
use App\Models\Farm;
use Illuminate\Database\Seeder;

/**
 * Garante que toda fazenda existente tenha a categoria "Compra de café".
 * Idempotente — pode rodar de novo sem duplicar.
 */
class AddCompraCafeCategorySeeder extends Seeder
{
    public function run(): void
    {
        Farm::query()->each(function (Farm $farm) {
            ExpenseCategory::query()
                ->withoutGlobalScopes()
                ->firstOrCreate(
                    ['farm_id' => $farm->id, 'nome' => ExpenseCategory::COMPRA_CAFE],
                    ['ativo' => true]
                );
        });
    }
}
