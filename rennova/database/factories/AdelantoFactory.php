<?php

namespace Database\Factories;

use App\Models\Adelanto;
use App\Models\Empleado;
use Illuminate\Database\Eloquent\Factories\Factory;

class AdelantoFactory extends Factory
{
    protected $model = Adelanto::class;

    public function definition(): array
    {
        return [
            'id_empleado' => Empleado::factory(),
            'monto' => $this->faker->randomFloat(2, 100, 5000),
            'fecha_emision' => $this->faker->dateTimeBetween('-3 months', 'now'),
            'estado' => 'pendiente',
        ];
    }

    public function pagado(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => 'pagado',
        ]);
    }

    public function pendiente(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => 'pendiente',
        ]);
    }
}
