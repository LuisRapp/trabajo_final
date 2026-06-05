<?php

namespace Database\Factories;

use App\Enums\TaskType;
use App\Models\Lote;
use Illuminate\Database\Eloquent\Factories\Factory;

class LoteFactory extends Factory
{
    protected $model = Lote::class;

    public function definition(): array
    {
        return [
            'propietario' => $this->faker->company(),
            'condicion_compra' => $this->faker->randomElement(['propio', 'alquilado']),
            'estado' => 'activo',
            'ubicacion' => $this->faker->city(),
            'especie' => $this->faker->randomElement(['Pino elliottii', 'Eucalipto grandis', 'Álamo', 'Sauce criollo', 'Pino taeda']),
            'superficie' => $this->faker->randomFloat(2, 5, 250),
            'latitud' => $this->faker->latitude(-34.50000000, -26.00000000),
            'longitud' => $this->faker->longitude(-63.00000000, -54.00000000),
            'main_task_type' => $this->faker->randomElement(TaskType::cases())->value,
        ];
    }

    public function activo(): static
    {
        return $this->state(fn () => ['estado' => 'activo']);
    }

    public function enProceso(): static
    {
        return $this->state(fn () => ['estado' => 'en_proceso']);
    }

    public function inactivo(): static
    {
        return $this->state(fn () => ['estado' => 'inactivo']);
    }

    public function cerrado(): static
    {
        return $this->state(fn () => ['estado' => 'cerrado']);
    }
}
