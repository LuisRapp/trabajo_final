<?php

namespace Database\Factories;

use App\Models\Cliente;
use App\Models\Empleado;
use App\Models\Proveedor;
use App\Models\Venta;
use Illuminate\Database\Eloquent\Factories\Factory;

class VentaFactory extends Factory
{
    protected $model = Venta::class;

    public function definition(): array
    {
        return [
            'id_empleado' => $this->faker->boolean(60) ? Empleado::factory() : null,
            'id_cliente' => Cliente::factory(),
            'id_proveedor' => $this->faker->boolean(25) ? Proveedor::factory() : null,
            'fecha_emision' => $this->faker->dateTimeBetween('-6 months', 'now'),
            'monto' => $this->faker->randomFloat(2, 150000, 2500000),
            'observaciones' => $this->faker->optional()->sentence(8),
        ];
    }
}
