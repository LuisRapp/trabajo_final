<?php

namespace Database\Factories;

use App\Models\Lote;
use App\Models\ParteDiario;
use Illuminate\Database\Eloquent\Factories\Factory;

class ParteDiarioFactory extends Factory
{
    protected $model = ParteDiario::class;

    public function definition(): array
    {
        $costoManoObra = $this->faker->randomFloat(2, 50000, 180000);
        $costoInsumos = $this->faker->randomFloat(2, 15000, 75000);
        $costoMaquinaria = $this->faker->randomFloat(2, 40000, 200000);
        $costoTotalDia = $costoManoObra + $costoInsumos + $costoMaquinaria;
        $volumenProducido = $this->faker->randomFloat(2, 20, 160); // toneladas movidas

        return [
            'id_lote' => Lote::factory(),
            'fecha' => $this->faker->dateTimeBetween('-6 months', 'now'),
            'es_dia_caido' => $this->faker->boolean(15),
            'observaciones' => $this->faker->optional()->sentence(10),
            'costo_mano_obra' => $costoManoObra,
            'costo_insumos' => $costoInsumos,
            'costo_maquinaria' => $costoMaquinaria,
            'costo_total_dia' => $costoTotalDia,
            'costo_unitario_calculado' => $this->faker->randomFloat(2, 300, 1500) + ($volumenProducido > 0 ? $costoTotalDia / max($volumenProducido, 1) : 0),
        ];
    }
}
