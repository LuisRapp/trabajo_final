<?php

namespace Database\Factories;

use App\Models\CategoriaClientePrecio;
use App\Models\CategoriaMadera;
use App\Models\Cliente;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoriaClientePrecioFactory extends Factory
{
    protected $model = CategoriaClientePrecio::class;

    public function definition(): array
    {
        return [
            'cliente_id' => Cliente::factory(),
            'categoria_id' => CategoriaMadera::factory(),
            'precio' => $this->faker->randomFloat(2, 50, 500),
            'fecha_desde' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'fecha_hasta' => null,
        ];
    }

    public function vigente(): static
    {
        return $this->state(fn () => [
            'fecha_desde' => now()->subMonth(),
            'fecha_hasta' => null,
        ]);
    }

    public function vencido(): static
    {
        return $this->state(fn () => [
            'fecha_desde' => now()->subYear(),
            'fecha_hasta' => now()->subMonth(),
        ]);
    }
}
