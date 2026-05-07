<?php

use App\Models\Customer;
use App\Models\Movement;
use App\Models\Secagem;
use App\Models\SecagemItem;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function () {
            // 1) Movements legados que apontam pra SecagemItem passam a apontar pra Secagem
            $legacy = Movement::query()
                ->where('source_type', SecagemItem::class)
                ->whereNotNull('source_id')
                ->get(['id', 'source_id']);

            foreach ($legacy as $m) {
                $item = SecagemItem::withoutGlobalScopes()->find($m->source_id);
                if ($item) {
                    Movement::withoutEvents(function () use ($m, $item) {
                        Movement::query()->whereKey($m->id)->update([
                            'source_type' => Secagem::class,
                            'source_id' => $item->secagem_id,
                        ]);
                    });
                }
            }

            // 2) Cliente com saldo > 0 e zero movements ganha 1 entrada "Saldo inicial"
            Customer::withoutGlobalScopes()
                ->where('saldo_cafe_kg', '>', 0)
                ->doesntHave('movements')
                ->chunkById(200, function ($customers) {
                    foreach ($customers as $customer) {
                        Movement::create([
                            'farm_id' => $customer->farm_id,
                            'customer_id' => $customer->id,
                            'user_id' => null,
                            'tipo' => Movement::TIPO_ENTRADA,
                            'quantidade_kg' => (float) $customer->saldo_cafe_kg,
                            'observacao' => 'Saldo inicial',
                            'occurred_at' => $customer->created_at ?? now(),
                        ]);
                    }
                });
        });
    }

    public function down(): void
    {
        // Não reverte: dados de auditoria não devem ser apagados em rollback.
    }
};
