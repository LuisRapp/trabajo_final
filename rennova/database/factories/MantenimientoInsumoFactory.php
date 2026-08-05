<?php

namespace Database\Factories;

use App\Models\Insumo;
use App\Models\Mantenimiento;
use App\Models\MantenimientoInsumo;
use Illuminate\Database\Eloquent\Factories\Factory;

class MantenimientoInsumoFactory extends Factory
{
    protected $model = MantenimientoInsumo::class;

    public function definition(): array
    {
        $cantidad = $this->faker->randomFloat(2, 1, 20);
        $costoUnitario = $this->faker->randomFloat(2, 50, 1200);

        return [
            'id_mantenimiento' => Mantenimiento::factory(),
            'id_insumo' => Insumo::factory(),
            'cantidad_utilizada' => $cantidad,
            'costo_unitario' => $costoUnitario,
            'subtotal' => $cantidad * $costoUnitario,
        ];
    }
}
