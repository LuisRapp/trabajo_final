<?php

namespace Database\Factories;

use App\Models\Carga;
use App\Models\Lote;
use Illuminate\Database\Eloquent\Factories\Factory;

class CargaFactory extends Factory
{
    protected $model = Carga::class;

    public function definition(): array
    {
        $pesoBruto = $this->faker->randomFloat(2, 18000, 32000);
        $tara = $this->faker->randomFloat(2, 7000, 12000);
        $pesoNeto = max($pesoBruto - $tara, 8000);

        return [
            'id_lote' => Lote::factory(),
            'id_parte_diario' => null,
            'id_categoria_madera' => null,
            'ticket' => $this->faker->unique()->numerify('TK-#####'),
            'peso_bruto' => $pesoBruto,
            'tara' => $tara,
            'peso_neto' => $pesoNeto,
            'destino' => $this->faker->randomElement(['Aserradero Sur', 'Planta de chips', 'Depósito central', 'Cliente final']),
            'fecha_carga' => $this->faker->dateTimeBetween('-3 months', 'now'),
        ];
    }
}
