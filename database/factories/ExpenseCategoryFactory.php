<?php

namespace Database\Factories;

use App\Models\ExpenseCategory;
use App\Models\Farm;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExpenseCategoryFactory extends Factory
{
    protected $model = ExpenseCategory::class;

    public function definition(): array
    {
        static $i = 0;
        $i++;
        return [
            'farm_id' => Farm::factory(),
            'nome' => 'Categoria ' . $i,
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
