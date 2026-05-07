<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('farms', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 150);
            $table->string('slug', 180)->unique();
            $table->enum('status', ['trial', 'active', 'past_due', 'blocked'])->default('trial');
            $table->string('telefone', 30)->nullable();
            $table->string('cidade', 120)->nullable();
            $table->char('estado', 2)->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('farms');
    }
};
