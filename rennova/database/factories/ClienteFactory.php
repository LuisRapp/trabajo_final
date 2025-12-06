<?php

namespace Database\Factories;

use App\Models\Cliente;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClienteFactory extends Factory
{
    protected $model = Cliente::class;

    public function definition(): array
    {
        return [
            'razon_social' => $this->faker->company(),
            'cuit' => $this->faker->unique()->numerify('##-########-#'),
            'direccion' => $this->faker->address(),
            'contacto' => $this->faker->name(),
        ];
    }
}
