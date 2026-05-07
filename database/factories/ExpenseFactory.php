<?php

namespace Database\Factories;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Farm;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExpenseFactory extends Factory
{
    protected $model = Expense::class;

    public function definition(): array
    {
        $qtd = fake()->randomFloat(2, 1, 50);
        $unit = fake()->randomFloat(2, 5, 200);
        return [
            'farm_id' => Farm::factory(),
            'user_id' => null,
            'expense_category_id' => null,
            'data' => fake()->dateTimeBetween('-60 days', 'now')->format('Y-m-d'),
            'descricao' => fake()->sentence(3),
            'unidade' => fake()->randomElement(['L', 'kg', 'h', 'un']),
            'quantidade' => $qtd,
            'valor_unitario' => $unit,
            'valor_total' => round($qtd * $unit, 2),
        ];
    }

    public function forFarm(Farm $farm): static
    {
        return $this->state(fn () => ['farm_id' => $farm->id]);
    }

    public function category(ExpenseCategory $cat): static
    {
        return $this->state(fn () => ['expense_category_id' => $cat->id, 'farm_id' => $cat->farm_id]);
    }
}
