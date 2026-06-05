<?php

namespace Database\Factories;

use App\Models\Empleado;
use App\Models\PropuestaAsignacion;
use App\Models\PropuestaAsignacionEmpleado;
use Illuminate\Database\Eloquent\Factories\Factory;

class PropuestaAsignacionEmpleadoFactory extends Factory
{
    protected $model = PropuestaAsignacionEmpleado::class;

    public function definition(): array
    {
        return [
            'id_allocation_proposal' => PropuestaAsignacion::factory(),
            'id_empleado' => Empleado::factory(),
            'rol_sugerido' => $this->faker->randomElement(['operador', 'capataz', 'ayudante']),
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
