<?php

use App\Models\Area;
use App\Models\Customer;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Saldos por dono de estoque:
 *  - Customer: saldo_cafe_kg vira saldo_coco_kg + novo saldo_seco_kg
 *  - Area: ganha saldo_coco_kg + saldo_seco_kg (produção própria por talhão)
 *  - Farm: ganha saldo_seco_comissao_kg (acúmulo das comissões do serviço)
 *
 * Precisão decimal:12,2 (padrão visual do sistema, 2 casas).
 *
 * Backfill da Area: soma quantidade_seca_kg dos items das secagens concluídas
 * vinculadas à área (Secagem.area_id legado), atribuindo ao saldo_seco_kg.
 * Saldo de côco da área começa em 0 (sem histórico de colheita pré-feature).
 */
return new class extends Migration
{
    public function up(): void
    {
        // Customer: rename + novo
        Schema::table('customers', function (Blueprint $table) {
            $table->renameColumn('saldo_cafe_kg', 'saldo_coco_kg');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->decimal('saldo_coco_kg', 12, 2)->default(0)->change();
            $table->decimal('saldo_seco_kg', 12, 2)->default(0)->after('saldo_coco_kg');
        });

        // Area: novos saldos
        Schema::table('areas', function (Blueprint $table) {
            $table->decimal('saldo_coco_kg', 12, 2)->default(0)->after('ativo');
            $table->decimal('saldo_seco_kg', 12, 2)->default(0)->after('saldo_coco_kg');
        });

        // Farm: saldo de comissão
        Schema::table('farms', function (Blueprint $table) {
            $table->decimal('saldo_seco_comissao_kg', 12, 2)->default(0)->after('status');
        });

        // Backfill: produção retroativa das áreas
        // Soma quantidade_seca_kg de items pertencentes a secagens com area_id (legado).
        if (Schema::hasColumn('secagens', 'area_id')) {
            $rows = DB::table('secagem_items as si')
                ->join('secagens as s', 's.id', '=', 'si.secagem_id')
                ->where('s.status', 'concluida')
                ->whereNotNull('s.area_id')
                ->select('s.area_id', DB::raw('SUM(si.quantidade_seca_kg) as total'))
                ->groupBy('s.area_id')
                ->get();

            foreach ($rows as $r) {
                DB::table('areas')->where('id', $r->area_id)->update([
                    'saldo_seco_kg' => $r->total ?? 0,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('saldo_seco_kg');
            $table->renameColumn('saldo_coco_kg', 'saldo_cafe_kg');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->decimal('saldo_cafe_kg', 12, 3)->default(0)->change();
        });

        Schema::table('areas', function (Blueprint $table) {
            $table->dropColumn(['saldo_coco_kg', 'saldo_seco_kg']);
        });

        Schema::table('farms', function (Blueprint $table) {
            $table->dropColumn('saldo_seco_comissao_kg');
        });
    }
};
