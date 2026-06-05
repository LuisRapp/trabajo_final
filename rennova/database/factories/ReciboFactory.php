<?php

namespace Database\Factories;

use App\Models\Empleado;
use App\Models\Recibo;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReciboFactory extends Factory
{
    protected $model = Recibo::class;

    public function definition(): array
    {
        return [
            'id_empleado' => Empleado::factory(),
            'fecha_emision' => now(),
            'monto_bruto' => $this->faker->randomFloat(2, 1000, 50000),
            'descuentos' => 0,
            'monto' => $this->faker->randomFloat(2, 1000, 50000),
            'observaciones' => $this->faker->optional()->sentence(),
        ];
    }
}
