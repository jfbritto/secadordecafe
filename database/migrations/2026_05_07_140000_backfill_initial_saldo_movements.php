<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Backfill legado de saldos iniciais. Mantido pra histórico em produção.
 *
 * IMPORTANTE: usa DB::table puro pra não depender da forma atual dos models —
 * essa migration roda ANTES da polimorfização do Movement, então não pode
 * usar Customer::movements() (que aponta pra owner_id polimórfico hoje).
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function () {
            // 1) Movements legados apontando pra SecagemItem passam a apontar pra Secagem
            $legacy = DB::table('movements')
                ->where('source_type', 'App\\Models\\SecagemItem')
                ->whereNotNull('source_id')
                ->get(['id', 'source_id']);

            foreach ($legacy as $m) {
                $item = DB::table('secagem_items')->where('id', $m->source_id)->first();
                if ($item) {
                    DB::table('movements')->where('id', $m->id)->update([
                        'source_type' => 'App\\Models\\Secagem',
                        'source_id' => $item->secagem_id,
                    ]);
                }
            }

            // 2) Cliente com saldo > 0 e zero movements ganha 1 entrada "Saldo inicial"
            DB::table('customers')
                ->where('saldo_cafe_kg', '>', 0)
                ->whereNotExists(function ($q) {
                    $q->select(DB::raw(1))
                        ->from('movements')
                        ->whereColumn('movements.customer_id', 'customers.id');
                })
                ->orderBy('id')
                ->chunkById(200, function ($customers) {
                    foreach ($customers as $customer) {
                        DB::table('movements')->insert([
                            'farm_id' => $customer->farm_id,
                            'customer_id' => $customer->id,
                            'user_id' => null,
                            'tipo' => 'entrada',
                            'quantidade_kg' => $customer->saldo_cafe_kg,
                            'observacao' => 'Saldo inicial',
                            'occurred_at' => $customer->created_at ?? now(),
                            'created_at' => now(),
                            'updated_at' => now(),
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
