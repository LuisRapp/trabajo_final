<?php

namespace Database\Factories;

use App\Models\Insumo;
use App\Models\LoteInventario;
use App\Models\Proveedor;
use Illuminate\Database\Eloquent\Factories\Factory;

class LoteInventarioFactory extends Factory
{
    protected $model = LoteInventario::class;

    public function definition(): array
    {
        $cantidadInicial = $this->faker->randomFloat(2, 50, 1000);

        return [
            'id_insumo' => Insumo::factory(),
            'id_proveedor' => Proveedor::factory(),
            'cantidad_inicial' => $cantidadInicial,
            'cantidad_disponible' => $cantidadInicial,
            'precio_unitario' => $this->faker->randomFloat(2, 10, 500),
            'costo_total' => $cantidadInicial * $this->faker->randomFloat(2, 10, 500),
            'fecha_compra' => $this->faker->dateTimeBetween('-6 months', 'now'),
            'numero_factura' => $this->faker->optional()->numerify('FA-#####'),
            'tipo_movimiento' => 'compra',
            'observaciones' => $this->faker->optional()->sentence(4),
            'agotado' => false,
        ];
    }

    public function disponible(): static
    {
        return $this->state(fn () => ['agotado' => false]);
    }

    public function agotado(): static
    {
        return $this->state(fn () => [
            'agotado' => true,
            'cantidad_disponible' => 0,
        ]);
    }

    public function proximoAgotar(): static
    {
        return $this->state(function () {
            $cantidadInicial = 100;

            return [
                'cantidad_inicial' => $cantidadInicial,
                'cantidad_disponible' => 15,
                'agotado' => false,
            ];
        });
    }
}
