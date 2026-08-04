<?php

namespace Database\Factories;

use App\Models\TipoMantenimiento;
use Illuminate\Database\Eloquent\Factories\Factory;

class TipoMantenimientoFactory extends Factory
{
    protected $model = TipoMantenimiento::class;

    public function definition(): array
    {
        return [
            'nombre' => $this->faker->randomElement(['Preventivo', 'Correctivo', 'Preventivo 500hs', 'Correctivo Mayor']),
        ];
    }

    public function preventivo(): static
    {
        return $this->state(fn () => ['nombre' => 'Preventivo '.$this->faker->numberBetween(100, 1000).'hs']);
    }

    public function correctivo(): static
    {
        return $this->state(fn () => ['nombre' => 'Correctivo '.$this->faker->randomElement(['Motor', 'Frenos', 'Hidraulico'])]);
    }
}
