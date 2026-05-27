<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Apelido opcional pra secagem — referência livre que o produtor usa pra
 * lembrar a operação ao bater o olho na listagem ("Café do compadre",
 * "Mutirão de junho"). Quando vazio, a UI cai pro resumo dos participantes.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('secagens', function (Blueprint $table) {
            $table->string('apelido', 80)->nullable()->after('numero');
        });
    }

    public function down(): void
    {
        Schema::table('secagens', function (Blueprint $table) {
            $table->dropColumn('apelido');
        });
    }
};
