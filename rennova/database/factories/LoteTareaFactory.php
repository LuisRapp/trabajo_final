<?php

namespace Database\Factories;

use App\Enums\TaskType;
use App\Models\Lote;
use App\Models\LoteTarea;
use Illuminate\Database\Eloquent\Factories\Factory;

class LoteTareaFactory extends Factory
{
    protected $model = LoteTarea::class;

    public function definition(): array
    {
        return [
            'id_lote' => Lote::factory(),
            'tipo_tarea' => $this->faker->randomElement(TaskType::cases())->value,
            'estado' => 'planificada',
            'fecha_inicio' => $this->faker->dateTimeBetween('now', '+3 months'),
            'fecha_fin' => null,
            'superficie_afectada_ha' => $this->faker->randomFloat(2, 1, 50),
        ];
    }

    public function planificada(): static
    {
        return $this->state(fn () => ['estado' => 'planificada']);
    }

    public function enEjecucion(): static
    {
        return $this->state(fn () => ['estado' => 'en_ejecucion']);
    }

    public function cerrada(): static
    {
        return $this->state(fn () => ['estado' => 'cerrada']);
    }
}
