<?php

namespace Database\Factories;

use App\Models\Farm;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class FarmFactory extends Factory
{
    protected $model = Farm::class;

    public function definition(): array
    {
        $nome = 'Fazenda ' . fake()->lastName();
        return [
            'nome' => $nome,
            'slug' => Str::slug($nome) . '-' . fake()->unique()->numberBetween(1, 99999),
            'status' => Farm::STATUS_TRIAL,
            'telefone' => fake()->phoneNumber(),
            'cidade' => fake()->city(),
            'estado' => 'MG',
            'trial_ends_at' => now()->addDays(14),
        ];
    }

    public function blocked(): static
    {
        return $this->state(fn () => ['status' => Farm::STATUS_BLOCKED]);
    }

    public function active(): static
    {
        return $this->state(fn () => ['status' => Farm::STATUS_ACTIVE, 'trial_ends_at' => null]);
    }
}
