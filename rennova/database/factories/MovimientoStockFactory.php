<?php

namespace Database\Factories;

use App\Models\Insumo;
use App\Models\MovimientoStock;
use Illuminate\Database\Eloquent\Factories\Factory;

class MovimientoStockFactory extends Factory
{
    protected $model = MovimientoStock::class;

    public function definition(): array
    {
        return [
            'id_insumo' => Insumo::factory(),
            'tipo' => $this->faker->randomElement(['entrada', 'salida']),
            'cantidad' => $this->faker->randomFloat(2, 1, 100),
            'fecha' => $this->faker->dateTimeBetween('-1 month', 'now')->format('Y-m-d'),
            'motivo' => $this->faker->randomElement(['Compra', 'Ajuste', 'Devolución', 'Mantenimiento']),
            'precio_unitario' => $this->faker->randomFloat(2, 10, 500),
            'costo_total_movimiento' => null,
        ];
    }

    public function entrada(): static
    {
        return $this->state(fn (array $attributes) => [
            'tipo' => 'entrada',
            'costo_total_movimiento' => $attributes['cantidad'] * $attributes['precio_unitario'],
        ]);
    }

    public function salida(): static
    {
        return $this->state(fn (array $attributes) => [
            'tipo' => 'salida',
            'costo_total_movimiento' => $attributes['cantidad'] * $attributes['precio_unitario'],
        ]);
    }
}
