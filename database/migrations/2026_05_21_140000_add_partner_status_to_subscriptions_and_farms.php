<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Adiciona o status 'partner' aos ENUMs de `farms.status` e `subscriptions.status`.
 *
 * O ALTER COLUMN ENUM é específico do MySQL — em SQLite (testes locais antigos)
 * ENUMs viram TEXT sem constraint, então o ALTER é no-op. Como a CI roda contra
 * MySQL real, garantimos compatibilidade em prod.
 */
return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        if ($driver !== 'mysql') {
            return; // SQLite armazena enum como TEXT, qualquer string passa
        }

        DB::statement("ALTER TABLE farms MODIFY status ENUM('trial','active','past_due','blocked','partner') NOT NULL DEFAULT 'trial'");
        DB::statement("ALTER TABLE subscriptions MODIFY status ENUM('trial','active','past_due','canceled','blocked','partner') NOT NULL DEFAULT 'trial'");
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        if ($driver !== 'mysql') {
            return;
        }

        // Volta a fazendas que estavam como 'partner' pra 'active' antes de remover do enum
        DB::table('farms')->where('status', 'partner')->update(['status' => 'active']);
        DB::table('subscriptions')->where('status', 'partner')->update(['status' => 'active']);

        DB::statement("ALTER TABLE farms MODIFY status ENUM('trial','active','past_due','blocked') NOT NULL DEFAULT 'trial'");
        DB::statement("ALTER TABLE subscriptions MODIFY status ENUM('trial','active','past_due','canceled','blocked') NOT NULL DEFAULT 'trial'");
    }
};
