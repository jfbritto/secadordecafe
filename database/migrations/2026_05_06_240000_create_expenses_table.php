<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->constrained('farms')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('data');
            $table->string('descricao', 200);
            $table->enum('categoria', [
                'combustivel', 'manutencao', 'mao_de_obra',
                'impostos', 'equipamentos', 'outros',
            ]);
            $table->string('unidade', 20)->nullable();
            $table->decimal('quantidade', 12, 3)->default(1);
            $table->decimal('valor_unitario', 12, 2)->default(0);
            $table->decimal('valor_total', 14, 2);
            $table->text('observacoes')->nullable();
            $table->timestamps();

            $table->index(['farm_id', 'data']);
            $table->index(['farm_id', 'categoria']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
