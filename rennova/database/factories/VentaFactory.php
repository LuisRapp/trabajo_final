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
            'id_empleado' => $this->faker->optional(0.6)->lazy(fn () => Empleado::factory()->create()->id_empleado),
            'id_cliente' => $this->faker->optional(0.8)->lazy(fn () => Cliente::factory()->create()->id_cliente),
            'id_proveedor' => $this->faker->optional(0.25)->lazy(fn () => Proveedor::factory()->create()->id_proveedor),
            'fecha_emision' => $this->faker->dateTimeBetween('-6 months', 'now'),
            'monto' => $this->faker->randomFloat(2, 150000, 2500000),
            'observaciones' => $this->faker->optional()->sentence(8),
            'activo' => $this->faker->boolean(90),
        ];
    }
}
