<?php

namespace Database\Factories;

use App\Models\Insumo;
use App\Models\LoteInventario;
use App\Models\MovimientoStock;
use Illuminate\Database\Eloquent\Factories\Factory;

class MovimientoStockFactory extends Factory
{
    protected $model = MovimientoStock::class;

    public function definition(): array
    {
        $insumo = Insumo::factory();
        $cantidad = $this->faker->randomFloat(2, 1, 100);
        $precioUnitario = $this->faker->randomFloat(2, 10, 500);

        return [
            'id_insumo' => $insumo,
            'tipo' => 'entrada',
            'cantidad' => $cantidad,
            'fecha' => $this->faker->dateTimeBetween('-6 months', 'now'),
            'motivo' => $this->faker->sentence(3),
            'precio_unitario' => $precioUnitario,
            'id_lote_inventario' => LoteInventario::factory(['id_insumo' => $insumo]),
            'costo_total_movimiento' => $cantidad * $precioUnitario,
            'id_parte_diario' => null,
        ];
    }

    public function entrada(): static
    {
        return $this->state(fn () => ['tipo' => 'entrada']);
    }

    public function salida(): static
    {
        return $this->state(fn () => ['tipo' => 'salida']);
    }
}
