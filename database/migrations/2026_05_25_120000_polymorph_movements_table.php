<?php

use App\Models\Customer;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Polimorfiza Movement pra suportar 3 donos de estoque: Customer, Area, Farm.
 * Adiciona o campo `produto` (coco|seco) e amplia o enum de `tipo`.
 *
 * Backfill: movements existentes são todos Customer + produto=coco.
 *
 * Idempotente: cada step verifica se já foi aplicado, pra suportar retomada
 * caso uma execução anterior tenha parado no meio.
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1) Drop FK do customer (se ainda existir) e o índice composto que dependia dela
        if ($this->foreignKeyExists('movements', 'movements_customer_id_foreign')) {
            Schema::table('movements', function (Blueprint $table) {
                $table->dropForeign('movements_customer_id_foreign');
            });
        }
        if ($this->indexExists('movements', 'movements_customer_id_occurred_at_index')) {
            Schema::table('movements', function (Blueprint $table) {
                $table->dropIndex('movements_customer_id_occurred_at_index');
            });
        }

        // 2) Adiciona owner polimórfico + produto (nullable inicialmente)
        if (! Schema::hasColumn('movements', 'owner_type')) {
            Schema::table('movements', function (Blueprint $table) {
                $table->string('owner_type')->nullable()->after('farm_id');
            });
        }
        if (! Schema::hasColumn('movements', 'owner_id')) {
            Schema::table('movements', function (Blueprint $table) {
                $table->unsignedBigInteger('owner_id')->nullable()->after('owner_type');
            });
        }
        if (! Schema::hasColumn('movements', 'produto')) {
            Schema::table('movements', function (Blueprint $table) {
                $table->enum('produto', ['coco', 'seco'])->default('coco')->after('owner_id');
            });
        }

        // 3) Backfill (só se ainda tem customer_id pra copiar)
        if (Schema::hasColumn('movements', 'customer_id')) {
            DB::table('movements')->whereNull('owner_type')->update([
                'owner_type' => Customer::class,
                'owner_id' => DB::raw('customer_id'),
                'produto' => 'coco',
            ]);
        }

        // 4) Amplia enum de tipo
        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE movements MODIFY tipo VARCHAR(20) NOT NULL");
        }

        // 5) Torna owner_* NOT NULL + drop customer_id
        Schema::table('movements', function (Blueprint $table) {
            $table->string('owner_type')->nullable(false)->change();
            $table->unsignedBigInteger('owner_id')->nullable(false)->change();
        });
        if (Schema::hasColumn('movements', 'customer_id')) {
            Schema::table('movements', function (Blueprint $table) {
                $table->dropColumn('customer_id');
            });
        }

        // 6) Novos índices (criar antes de tentar dropar o antigo, pra não esbarrar na FK)
        if (! $this->indexExists('movements', 'movements_owner_idx')) {
            Schema::table('movements', function (Blueprint $table) {
                $table->index(['owner_type', 'owner_id', 'occurred_at'], 'movements_owner_idx');
            });
        }
        if (! $this->indexExists('movements', 'movements_farm_prod_tipo_idx')) {
            Schema::table('movements', function (Blueprint $table) {
                $table->index(['farm_id', 'produto', 'tipo', 'occurred_at'], 'movements_farm_prod_tipo_idx');
            });
        }

        // 7) Agora dá pra dropar o índice antigo de (farm_id, tipo, occurred_at) — a FK
        // farm_id_foreign pode usar o novo índice composto que começa com farm_id.
        if ($this->indexExists('movements', 'movements_farm_id_tipo_occurred_at_index')) {
            Schema::table('movements', function (Blueprint $table) {
                $table->dropIndex('movements_farm_id_tipo_occurred_at_index');
            });
        }
    }

    public function down(): void
    {
        Schema::table('movements', function (Blueprint $table) {
            $table->dropIndex('movements_owner_idx');
            $table->dropIndex('movements_farm_prod_tipo_idx');
        });

        Schema::table('movements', function (Blueprint $table) {
            $table->foreignId('customer_id')->nullable()->after('farm_id');
        });

        DB::table('movements')
            ->where('owner_type', Customer::class)
            ->update(['customer_id' => DB::raw('owner_id')]);

        Schema::table('movements', function (Blueprint $table) {
            $table->dropColumn(['owner_type', 'owner_id', 'produto']);
            $table->foreignId('customer_id')->nullable(false)->change();
            $table->foreign('customer_id')->references('id')->on('customers')->cascadeOnDelete();
            $table->index(['customer_id', 'occurred_at']);
            $table->index(['farm_id', 'tipo', 'occurred_at']);
        });

        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE movements MODIFY tipo ENUM('entrada','secagem','ajuste','saida') NOT NULL");
        }
    }

    private function foreignKeyExists(string $table, string $name): bool
    {
        $database = DB::getDatabaseName();
        $row = DB::selectOne(
            'SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS
             WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND CONSTRAINT_NAME = ? AND CONSTRAINT_TYPE = "FOREIGN KEY"',
            [$database, $table, $name]
        );
        return $row !== null;
    }

    private function indexExists(string $table, string $name): bool
    {
        $database = DB::getDatabaseName();
        $row = DB::selectOne(
            'SELECT INDEX_NAME FROM information_schema.STATISTICS
             WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND INDEX_NAME = ? LIMIT 1',
            [$database, $table, $name]
        );
        return $row !== null;
    }
};
