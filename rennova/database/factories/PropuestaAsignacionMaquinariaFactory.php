<?php

namespace Database\Factories;

use App\Models\Maquinaria;
use App\Models\PropuestaAsignacion;
use App\Models\PropuestaAsignacionMaquinaria;
use Illuminate\Database\Eloquent\Factories\Factory;

class PropuestaAsignacionMaquinariaFactory extends Factory
{
    protected $model = PropuestaAsignacionMaquinaria::class;

    public function definition(): array
    {
        return [
            'id_allocation_proposal' => PropuestaAsignacion::factory(),
            'id_maquinaria' => Maquinaria::factory(),
            'tipo_sugerido' => $this->faker->randomElement(['Harvester', 'Forwarder', 'Skidder']),
            'score' => $this->faker->randomFloat(4, 0.5, 1.0),
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
