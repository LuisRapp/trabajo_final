<?php

namespace Database\Factories;

use App\Models\Insumo;
use App\Models\PropuestaAsignacion;
use App\Models\PropuestaAsignacionInsumo;
use Illuminate\Database\Eloquent\Factories\Factory;

class PropuestaAsignacionInsumoFactory extends Factory
{
    protected $model = PropuestaAsignacionInsumo::class;

    public function definition(): array
    {
        return [
            'id_allocation_proposal' => PropuestaAsignacion::factory(),
            'id_insumo' => Insumo::factory(),
            'cantidad_semana_1' => $this->faker->randomFloat(2, 1, 50),
            'costo_estimado_semana_1' => $this->faker->randomFloat(2, 500, 15000),
            'selected' => true,
        ];
    }

    public function selected(): static
    {
        return $this->state(fn () => ['selected' => true]);
    }

    public function notSelected(): static
    {
        return $this->state(fn () => ['selected' => false]);
    }
}
