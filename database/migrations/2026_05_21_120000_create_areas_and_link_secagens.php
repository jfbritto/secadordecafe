<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1) Tabela de áreas (talhões/lotes da roça).
        // Latitude/longitude opcionais — capturados via navigator.geolocation no form
        // (HTML5 Geolocation API, funciona em HTTPS).
        Schema::create('areas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->constrained('farms')->cascadeOnDelete();
            $table->string('nome', 120);
            $table->text('observacoes')->nullable();
            // Precisão suficiente pra ~1cm (lat: -90..90, lng: -180..180)
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();

            // Nome único por farm
            $table->unique(['farm_id', 'nome']);
            $table->index(['farm_id', 'ativo']);
        });

        // 2) Linka Secagem -> Area (opcional).
        // Se a área for excluída, mantém o histórico das secagens (SET NULL).
        Schema::table('secagens', function (Blueprint $table) {
            $table->foreignId('area_id')
                ->nullable()
                ->after('dryer_id')
                ->constrained('areas')
                ->nullOnDelete();

            $table->index(['farm_id', 'area_id', 'data']);
        });
    }

    public function down(): void
    {
        Schema::table('secagens', function (Blueprint $table) {
            $table->dropForeign(['area_id']);
            $table->dropIndex(['farm_id', 'area_id', 'data']);
            $table->dropColumn('area_id');
        });
        Schema::dropIfExists('areas');
    }
};
