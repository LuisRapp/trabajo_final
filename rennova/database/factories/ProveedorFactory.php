<?php

namespace Database\Factories;

use App\Models\Proveedor;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProveedorFactory extends Factory
{
    protected $model = Proveedor::class;

    public function definition(): array
    {
        return [
            'razon_social' => $this->faker->company(),
            'cuit' => $this->faker->unique()->numerify('##-########-#'),
            'direccion' => $this->faker->address(),
            'telefono' => $this->faker->unique()->numerify('11-########'),
            'email' => $this->faker->unique()->safeEmail(),
        ];
    }
}
