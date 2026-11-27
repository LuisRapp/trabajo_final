<?php

namespace Database\Factories;

use App\Models\TipoMantenimiento;
use Illuminate\Database\Eloquent\Factories\Factory;

class TipoMantenimientoFactory extends Factory
{
    protected $model = TipoMantenimiento::class;

    public function definition(): array
    {
        return $this->faker->randomElement([
            ['nombre' => 'Preventivo', 'comportamiento' => 'preventivo'],
            ['nombre' => 'Correctivo', 'comportamiento' => 'correctivo'],
            ['nombre' => 'Preventivo 500hs', 'comportamiento' => 'preventivo'],
            ['nombre' => 'Correctivo Mayor', 'comportamiento' => 'correctivo'],
        ]);
    }

    public function preventivo(): static
    {
        return $this->state(fn () => [
            'nombre' => 'Preventivo '.$this->faker->numberBetween(100, 1000).'hs',
            'comportamiento' => 'preventivo',
        ]);
    }

    public function correctivo(): static
    {
        return $this->state(fn () => [
            'nombre' => 'Correctivo '.$this->faker->randomElement(['Motor', 'Frenos', 'Hidraulico']),
            'comportamiento' => 'correctivo',
        ]);
    }
}
