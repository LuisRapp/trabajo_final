<?php

namespace Database\Factories;

use App\Models\UnidadMedida;
use Illuminate\Database\Eloquent\Factories\Factory;

class UnidadMedidaFactory extends Factory
{
    protected $model = UnidadMedida::class;

    public function definition(): array
    {
        return [
            'nombre' => $this->faker->randomElement(['Kilogramo', 'Litro', 'Metro cúbico', 'Unidad', 'Par']),
            'abreviatura' => $this->faker->randomElement(['kg', 'lt', 'm3', 'u', 'par']),
        ];
    }
}
