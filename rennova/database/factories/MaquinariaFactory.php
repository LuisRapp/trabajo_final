<?php

namespace Database\Factories;

use App\Models\Maquinaria;
use App\Models\TipoMaquinaria;
use Illuminate\Database\Eloquent\Factories\Factory;

class MaquinariaFactory extends Factory
{
    protected $model = Maquinaria::class;

    public function definition(): array
    {
        return [
            'id_tipo_maquinaria' => TipoMaquinaria::factory(),
            'modelo' => $this->faker->bothify('JD-####-?'),
            'estado' => $this->faker->randomElement(['activo', 'mantenimiento', 'fuera_servicio']),
            'es_alquilada' => $this->faker->boolean(25),
            'fecha_inicio_actividades' => $this->faker->dateTimeBetween('-5 years', 'now'),
            'toneladas_acumuladas' => $this->faker->randomFloat(2, 500, 25000),
            'umbral_toneladas' => $this->faker->numberBetween(5000, 35000),
        ];
    }
}
