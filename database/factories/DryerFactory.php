<?php

namespace Database\Factories;

use App\Models\Dryer;
use App\Models\Farm;
use Illuminate\Database\Eloquent\Factories\Factory;

class DryerFactory extends Factory
{
    protected $model = Dryer::class;

    public function definition(): array
    {
        static $i = 0;
        $i++;
        return [
            'farm_id' => Farm::factory(),
            'nome' => 'Secador ' . $i,
            'capacidade_kg' => fake()->randomElement([null, 1500, 2500, 5000]),
            'modelo' => fake()->randomElement([null, 'Pinhalense', 'Palini', 'D\'Andrea']),
            'ativo' => true,
        ];
    }

    public function forFarm(Farm $farm): static
    {
        return $this->state(fn () => ['farm_id' => $farm->id]);
    }

    public function inativo(): static
    {
        return $this->state(fn () => ['ativo' => false]);
    }
}
