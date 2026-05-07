<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Mapa antigo (enum slug) → nome amigável */
    private const LEGACY_LABELS = [
        'combustivel' => 'Combustível',
        'manutencao' => 'Manutenção',
        'mao_de_obra' => 'Mão de obra',
        'impostos' => 'Impostos',
        'equipamentos' => 'Equipamentos',
        'outros' => 'Outros',
    ];

    /** Categorias padrão semeadas em cada fazenda */
    private const DEFAULTS = [
        'Combustível', 'Manutenção', 'Mão de obra',
        'Impostos', 'Equipamentos', 'Outros',
    ];

    public function up(): void
    {
        Schema::create('expense_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->constrained('farms')->cascadeOnDelete();
            $table->string('nome', 80);
            $table->boolean('ativo')->default(true);
            $table->text('observacoes')->nullable();
            $table->timestamps();

            $table->unique(['farm_id', 'nome']);
            $table->index(['farm_id', 'ativo']);
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->foreignId('expense_category_id')->nullable()->after('user_id')
                  ->constrained('expense_categories')->nullOnDelete();
        });

        // Para cada fazenda existente: garantir as 6 categorias padrão.
        $farms = DB::table('farms')->pluck('id');
        foreach ($farms as $farmId) {
            $this->seedDefaultsForFarm($farmId);
        }

        // Backfill expenses.expense_category_id a partir do enum 'categoria' antigo.
        if (Schema::hasColumn('expenses', 'categoria')) {
            $rows = DB::table('expenses')
                ->select('id', 'farm_id', 'categoria')
                ->whereNotNull('categoria')
                ->get();

            foreach ($rows as $row) {
                $label = self::LEGACY_LABELS[$row->categoria] ?? ucfirst($row->categoria);
                $catId = DB::table('expense_categories')
                    ->where('farm_id', $row->farm_id)
                    ->where('nome', $label)
                    ->value('id');

                if (! $catId) {
                    $catId = DB::table('expense_categories')->insertGetId([
                        'farm_id' => $row->farm_id,
                        'nome' => $label,
                        'ativo' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                DB::table('expenses')->where('id', $row->id)->update(['expense_category_id' => $catId]);
            }

            // Drop index envolvendo 'categoria' antes de remover a coluna (SQLite exige).
            if (Schema::hasIndex('expenses', 'expenses_farm_id_categoria_index')) {
                Schema::table('expenses', function (Blueprint $table) {
                    $table->dropIndex('expenses_farm_id_categoria_index');
                });
            }

            Schema::table('expenses', function (Blueprint $table) {
                $table->dropColumn('categoria');
            });
        }
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            if (! Schema::hasColumn('expenses', 'categoria')) {
                $table->enum('categoria', [
                    'combustivel', 'manutencao', 'mao_de_obra',
                    'impostos', 'equipamentos', 'outros',
                ])->nullable()->after('user_id');
            }
        });

        // Restaurar enum a partir do nome (best effort)
        $reverse = array_flip(self::LEGACY_LABELS);
        DB::table('expenses')
            ->whereNotNull('expense_category_id')
            ->orderBy('id')
            ->each(function ($e) use ($reverse) {
                $cat = DB::table('expense_categories')->find($e->expense_category_id);
                $slug = $cat ? ($reverse[$cat->nome] ?? 'outros') : 'outros';
                DB::table('expenses')->where('id', $e->id)->update(['categoria' => $slug]);
            });

        Schema::table('expenses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('expense_category_id');
        });

        Schema::dropIfExists('expense_categories');
    }

    private function seedDefaultsForFarm(int $farmId): void
    {
        $now = now();
        foreach (self::DEFAULTS as $nome) {
            $exists = DB::table('expense_categories')
                ->where('farm_id', $farmId)
                ->where('nome', $nome)
                ->exists();

            if (! $exists) {
                DB::table('expense_categories')->insert([
                    'farm_id' => $farmId,
                    'nome' => $nome,
                    'ativo' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }
};
