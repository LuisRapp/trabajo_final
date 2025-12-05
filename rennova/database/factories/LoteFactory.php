<?php

namespace Database\Factories;

use App\Models\Lote;
use Illuminate\Database\Eloquent\Factories\Factory;

class LoteFactory extends Factory
{
    protected $model = Lote::class;

    public function definition(): array
    {
        return [
            'propietario' => $this->faker->company(),
            'condicion_compra' => $this->faker->randomElement(['propio', 'alquiler', 'contrato_mixto']),
            'estado' => $this->faker->randomElement(['activo', 'cerrado', 'baja']),
            'ubicacion' => $this->faker->city(),
            'especie' => $this->faker->randomElement(['Pino elliottii', 'Eucalipto grandis', 'Álamo', 'Sauce criollo', 'Pino taeda']),
            'superficie' => $this->faker->randomFloat(2, 5, 250),
            'latitud' => $this->faker->latitude(-34.50000000, -26.00000000),
            'longitud' => $this->faker->longitude(-63.00000000, -54.00000000),
        ];
    }
}
