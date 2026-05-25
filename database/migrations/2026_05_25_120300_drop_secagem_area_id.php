<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Remove Secagem.area_id — agora a "área da secagem" deriva dos items
 * (cada item tem origin polimórfico que pode ser Customer ou Area).
 *
 * Deve rodar DEPOIS do backfill em 2026_05_25_120200_saldos_por_dono
 * (que ainda lê secagens.area_id pra calcular produção retroativa).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('secagens', function (Blueprint $table) {
            $table->dropForeign(['area_id']);
            $table->dropIndex(['farm_id', 'area_id', 'data']);
            $table->dropColumn('area_id');
        });
    }

    public function down(): void
    {
        Schema::table('secagens', function (Blueprint $table) {
            $table->foreignId('area_id')
                ->nullable()
                ->after('dryer_id')
                ->constrained('areas')
                ->nullOnDelete();

            $table->index(['farm_id', 'area_id', 'data']);
        });
    }
};
