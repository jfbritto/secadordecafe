<?php

use App\Models\Area;
use App\Models\Farm;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Unifica o estoque próprio do produtor na Farm.
 *
 * Antes: Area tinha saldo_coco_kg + saldo_seco_kg (estoque por talhão).
 * Agora: Farm é o único dono do estoque próprio (saldo_coco_kg + saldo_seco_kg).
 *        Area volta a ser só metadata; a rastreabilidade de produção por
 *        talhão fica via `movements.area_id` (nullable).
 *
 * `farms.saldo_seco_comissao_kg` é renomeado pra `saldo_seco_kg` — comissões
 * passam a somar no mesmo pote do café próprio.
 *
 * Backfill:
 *   - movements com owner=Area → vira owner=Farm + area_id preenchido
 *   - somatório de saldos das areas vai pra farm
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Farm: renomear saldo_seco_comissao_kg → saldo_seco_kg, criar saldo_coco_kg
        Schema::table('farms', function (Blueprint $table) {
            $table->renameColumn('saldo_seco_comissao_kg', 'saldo_seco_kg');
        });
        Schema::table('farms', function (Blueprint $table) {
            $table->decimal('saldo_coco_kg', 12, 2)->default(0)->after('status');
        });

        // 2. Movements: adicionar area_id pra rastreabilidade de produção por talhão
        Schema::table('movements', function (Blueprint $table) {
            $table->unsignedBigInteger('area_id')->nullable()->after('owner_id');
            $table->foreign('area_id')->references('id')->on('areas')->nullOnDelete();
            $table->index(['farm_id', 'area_id', 'occurred_at'], 'movements_area_idx');
        });

        // 3. Backfill: movements com owner=Area passam pra Farm + area_id
        DB::table('movements')
            ->where('owner_type', Area::class)
            ->orderBy('id')
            ->chunkById(500, function ($movs) {
                foreach ($movs as $m) {
                    $area = DB::table('areas')->where('id', $m->owner_id)->first();
                    if (! $area) {
                        continue;
                    }
                    DB::table('movements')->where('id', $m->id)->update([
                        'owner_type' => Farm::class,
                        'owner_id' => $area->farm_id,
                        'area_id' => $area->id,
                    ]);
                }
            });

        // 4. Soma saldos das areas e adiciona na farm correspondente
        $sums = DB::table('areas')
            ->selectRaw('farm_id, COALESCE(SUM(saldo_coco_kg), 0) as coco, COALESCE(SUM(saldo_seco_kg), 0) as seco')
            ->groupBy('farm_id')
            ->get();

        foreach ($sums as $row) {
            DB::table('farms')->where('id', $row->farm_id)->update([
                'saldo_coco_kg' => DB::raw("saldo_coco_kg + {$row->coco}"),
                'saldo_seco_kg' => DB::raw("saldo_seco_kg + {$row->seco}"),
            ]);
        }

        // 5. Areas: dropa saldos (vira só metadata)
        Schema::table('areas', function (Blueprint $table) {
            $table->dropColumn(['saldo_coco_kg', 'saldo_seco_kg']);
        });
    }

    public function down(): void
    {
        Schema::table('areas', function (Blueprint $table) {
            $table->decimal('saldo_coco_kg', 12, 2)->default(0)->after('ativo');
            $table->decimal('saldo_seco_kg', 12, 2)->default(0)->after('saldo_coco_kg');
        });

        // Movimentações de Farm com area_id voltam a apontar pra Area
        DB::table('movements')
            ->where('owner_type', Farm::class)
            ->whereNotNull('area_id')
            ->orderBy('id')
            ->chunkById(500, function ($movs) {
                foreach ($movs as $m) {
                    DB::table('movements')->where('id', $m->id)->update([
                        'owner_type' => Area::class,
                        'owner_id' => $m->area_id,
                    ]);
                }
            });

        Schema::table('movements', function (Blueprint $table) {
            $table->dropForeign(['area_id']);
            $table->dropIndex('movements_area_idx');
            $table->dropColumn('area_id');
        });

        Schema::table('farms', function (Blueprint $table) {
            $table->dropColumn('saldo_coco_kg');
            $table->renameColumn('saldo_seco_kg', 'saldo_seco_comissao_kg');
        });
    }
};
