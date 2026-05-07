<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Farm;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        return [
            'farm_id' => Farm::factory(),
            'nome' => fake()->name(),
            'telefone' => fake()->phoneNumber(),
            'cpf_cnpj' => fake()->numerify('###.###.###-##'),
            'observacoes' => null,
            'saldo_cafe_kg' => 0,
        ];
    }

    public function forFarm(Farm $farm): static
    {
        return $this->state(fn () => ['farm_id' => $farm->id]);
    }
}
