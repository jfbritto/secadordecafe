<?php

use App\Models\Customer;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Polimorfiza SecagemItem pra suportar origem Customer OU Area no mesmo item.
 * Permite que uma secagem física misture lotes de clientes + áreas próprias.
 *
 * Torna nullable os campos que só são preenchidos no segundo momento
 * (saída do secador): quantidade_seca_kg, comissao_*, saldo_liquido_kg.
 *
 * Backfill: items existentes são todos Customer.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('secagem_items', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->dropIndex(['customer_id']);
        });

        Schema::table('secagem_items', function (Blueprint $table) {
            $table->string('origin_type')->nullable()->after('secagem_id');
            $table->unsignedBigInteger('origin_id')->nullable()->after('origin_type');
        });

        DB::table('secagem_items')->update([
            'origin_type' => Customer::class,
            'origin_id' => DB::raw('customer_id'),
        ]);

        Schema::table('secagem_items', function (Blueprint $table) {
            $table->string('origin_type')->nullable(false)->change();
            $table->unsignedBigInteger('origin_id')->nullable(false)->change();
            $table->dropColumn('customer_id');

            $table->decimal('quantidade_seca_kg', 12, 3)->nullable()->change();
            $table->decimal('comissao_percentual', 5, 2)->nullable()->change();
            $table->decimal('comissao_kg', 12, 3)->nullable()->change();
            $table->decimal('saldo_liquido_kg', 12, 3)->nullable()->change();
        });

        Schema::table('secagem_items', function (Blueprint $table) {
            $table->index(['origin_type', 'origin_id'], 'secagem_items_origin_idx');
        });
    }

    public function down(): void
    {
        Schema::table('secagem_items', function (Blueprint $table) {
            $table->dropIndex('secagem_items_origin_idx');
        });

        Schema::table('secagem_items', function (Blueprint $table) {
            $table->foreignId('customer_id')->nullable()->after('secagem_id');
        });

        DB::table('secagem_items')
            ->where('origin_type', Customer::class)
            ->update(['customer_id' => DB::raw('origin_id')]);

        Schema::table('secagem_items', function (Blueprint $table) {
            $table->dropColumn(['origin_type', 'origin_id']);
            $table->foreignId('customer_id')->nullable(false)->change();
            $table->foreign('customer_id')->references('id')->on('customers')->restrictOnDelete();
            $table->index('customer_id');

            $table->decimal('quantidade_seca_kg', 12, 3)->default(0)->nullable(false)->change();
            $table->decimal('comissao_percentual', 5, 2)->default(0)->nullable(false)->change();
            $table->decimal('comissao_kg', 12, 3)->default(0)->nullable(false)->change();
            $table->decimal('saldo_liquido_kg', 12, 3)->default(0)->nullable(false)->change();
        });
    }
};
