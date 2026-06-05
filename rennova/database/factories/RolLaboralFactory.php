<?php

namespace Database\Factories;

use App\Models\RolLaboral;
use Illuminate\Database\Eloquent\Factories\Factory;

class RolLaboralFactory extends Factory
{
    protected $model = RolLaboral::class;

    public function definition(): array
    {
        return [
            'nombre' => $this->faker->jobTitle(),
            'costo_diario' => 1500.00,
        ];
    }
}
