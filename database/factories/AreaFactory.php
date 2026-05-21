<?php

namespace Database\Factories;

use App\Models\Area;
use App\Models\Farm;
use Illuminate\Database\Eloquent\Factories\Factory;

class AreaFactory extends Factory
{
    protected $model = Area::class;

    public function definition(): array
    {
        static $i = 0;
        $i++;
        return [
            'farm_id' => Farm::factory(),
            'nome' => 'Área ' . $i,
            'observacoes' => null,
            'latitude' => null,
            'longitude' => null,
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

    public function comLocalizacao(float $lat = -19.916681, float $lng = -43.934493): static
    {
        return $this->state(fn () => ['latitude' => $lat, 'longitude' => $lng]);
    }
}
