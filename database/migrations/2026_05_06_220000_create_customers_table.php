<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->constrained('farms')->cascadeOnDelete();
            $table->string('nome', 150);
            $table->string('telefone', 30)->nullable();
            $table->string('cpf_cnpj', 20)->nullable();
            $table->text('observacoes')->nullable();
            $table->decimal('saldo_cafe_kg', 12, 3)->default(0);
            $table->timestamps();

            $table->index(['farm_id', 'nome']);
            $table->unique(['farm_id', 'cpf_cnpj']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
