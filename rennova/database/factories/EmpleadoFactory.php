<?php

namespace Database\Factories;

use App\Models\Empleado;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmpleadoFactory extends Factory
{
    protected $model = Empleado::class;

    public function definition(): array
    {
        return [
            'id_rol_laboral' => null,
            'dni' => $this->faker->unique()->numerify('##.###.###'),
            'apellido' => $this->faker->lastName(),
            'nombre' => $this->faker->firstName(),
            'fecha_nacimiento' => $this->faker->optional()->dateTimeBetween('-60 years', '-22 years'),
            'fecha_inicio_actividades' => $this->faker->dateTimeBetween('-4 years', '-1 month'),
            'fecha_fin_actividades' => $this->faker->optional(0.1)->dateTimeBetween('-3 months', 'now'),
        ];
    }
}
