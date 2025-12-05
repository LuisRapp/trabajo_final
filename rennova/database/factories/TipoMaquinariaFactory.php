<?php

namespace Database\Factories;

use App\Models\TipoMaquinaria;
use Illuminate\Database\Eloquent\Factories\Factory;

class TipoMaquinariaFactory extends Factory
{
    protected $model = TipoMaquinaria::class;

    public function definition(): array
    {
        return [
            'nombre' => $this->faker->randomElement([
                'Skidder forestal',
                'Harvester',
                'Forwarder',
                'Topadora',
                'Cargadora frontal',
                'Camión tolva',
            ]),
            'umbral_toneladas' => $this->faker->numberBetween(5000, 35000),
        ];
    }
}
