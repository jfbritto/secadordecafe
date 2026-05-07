<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('secagens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->constrained('farms')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('numero');
            $table->date('data');
            $table->string('secador', 80);
            $table->text('observacoes')->nullable();
            $table->enum('status', ['rascunho', 'concluida'])->default('rascunho');
            $table->timestamp('concluida_at')->nullable();
            $table->timestamps();

            $table->unique(['farm_id', 'numero']);
            $table->index(['farm_id', 'data']);
            $table->index('status');
        });

        Schema::create('secagem_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')->constrained('farms')->cascadeOnDelete();
            $table->foreignId('secagem_id')->constrained('secagens')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->restrictOnDelete();
            $table->decimal('quantidade_recebida_kg', 12, 3);
            $table->decimal('quantidade_seca_kg', 12, 3);
            $table->decimal('comissao_percentual', 5, 2)->default(0);
            $table->decimal('comissao_kg', 12, 3)->default(0);
            $table->decimal('saldo_liquido_kg', 12, 3)->default(0);
            $table->timestamps();

            $table->index('secagem_id');
            $table->index('customer_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('secagem_items');
        Schema::dropIfExists('secagens');
    }
};
