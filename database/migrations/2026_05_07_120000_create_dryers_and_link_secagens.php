<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dryers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->constrained('farms')->cascadeOnDelete();
            $table->string('nome', 80);
            $table->decimal('capacidade_kg', 12, 3)->nullable();
            $table->string('modelo', 80)->nullable();
            $table->text('observacoes')->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();

            $table->unique(['farm_id', 'nome']);
            $table->index(['farm_id', 'ativo']);
        });

        Schema::table('secagens', function (Blueprint $table) {
            $table->foreignId('dryer_id')->nullable()->after('user_id')->constrained('dryers')->nullOnDelete();
        });

        // Backfill: para cada (farm_id, secador) criar Dryer e linkar.
        if (Schema::hasColumn('secagens', 'secador')) {
            $rows = DB::table('secagens')
                ->select('farm_id', 'secador')
                ->whereNotNull('secador')
                ->where('secador', '!=', '')
                ->groupBy('farm_id', 'secador')
                ->get();

            foreach ($rows as $row) {
                $now = now();
                $dryerId = DB::table('dryers')->insertGetId([
                    'farm_id' => $row->farm_id,
                    'nome' => $row->secador,
                    'ativo' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                DB::table('secagens')
                    ->where('farm_id', $row->farm_id)
                    ->where('secador', $row->secador)
                    ->update(['dryer_id' => $dryerId]);
            }

            Schema::table('secagens', function (Blueprint $table) {
                $table->dropColumn('secador');
            });
        }
    }

    public function down(): void
    {
        Schema::table('secagens', function (Blueprint $table) {
            if (! Schema::hasColumn('secagens', 'secador')) {
                $table->string('secador', 80)->nullable()->after('user_id');
            }
        });

        // Restore secador text from dryer relation
        DB::table('secagens')
            ->whereNotNull('dryer_id')
            ->orderBy('id')
            ->each(function ($s) {
                $dryer = DB::table('dryers')->find($s->dryer_id);
                if ($dryer) {
                    DB::table('secagens')->where('id', $s->id)->update(['secador' => $dryer->nome]);
                }
            });

        Schema::table('secagens', function (Blueprint $table) {
            $table->dropConstrainedForeignId('dryer_id');
        });

        Schema::dropIfExists('dryers');
    }
};
