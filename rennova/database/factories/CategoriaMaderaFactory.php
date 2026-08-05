<?php

namespace Database\Factories;

use App\Models\CategoriaMadera;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoriaMaderaFactory extends Factory
{
    protected $model = CategoriaMadera::class;

    public function definition(): array
    {
        return [
            'nombre' => $this->faker->randomElement(['Aserrable', 'Chips', 'Postes', 'Tablas', 'Vigas']),
            'descripcion' => $this->faker->sentence(6),
        ];
    }
}
