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
            'nombre' => $this->faker->randomElement(['Preventivo', 'Correctivo', 'Inspección']),
        ];
    }

    public function preventivo(): self
    {
        return $this->state(['nombre' => 'Mantenimiento Preventivo']);
    }

    public function correctivo(): self
    {
        return $this->state(['nombre' => 'Mantenimiento Correctivo']);
    }
}
