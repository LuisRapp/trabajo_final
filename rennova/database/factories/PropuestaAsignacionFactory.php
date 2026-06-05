<?php

namespace Database\Factories;

use App\Enums\TaskType;
use App\Models\Lote;
use App\Models\PropuestaAsignacion;
use Illuminate\Database\Eloquent\Factories\Factory;

class PropuestaAsignacionFactory extends Factory
{
    protected $model = PropuestaAsignacion::class;

    public function definition(): array
    {
        return [
            'id_lote' => Lote::factory(),
            'tipo_tarea' => $this->faker->randomElement(TaskType::cases())->value,
            'especie' => 'Pino elliottii',
            'superficie_ha' => $this->faker->randomFloat(2, 5, 100),
            'estimated_person_days' => $this->faker->randomFloat(2, 10, 200),
            'estimated_machine_days' => $this->faker->randomFloat(2, 10, 200),
            'estimated_duration_days' => $this->faker->randomFloat(2, 5, 60),
            'suggested_team_size' => $this->faker->numberBetween(3, 15),
            'suggested_machinery_count' => $this->faker->numberBetween(1, 5),
            'meta' => ['confidence' => 'normal'],
            'status' => 'draft',
        ];
    }

    public function draft(): static
    {
        return $this->state(fn () => ['status' => 'draft']);
    }

    public function confirmed(): static
    {
        return $this->state(fn () => [
            'status' => 'confirmed',
            'confirmed_at' => now(),
        ]);
    }

    public function applied(): static
    {
        return $this->state(fn () => [
            'status' => 'applied',
            'confirmed_at' => now(),
            'applied_at' => now(),
        ]);
    }

    public function closed(): static
    {
        return $this->state(fn () => ['status' => 'closed']);
    }

    public function lowConfidence(): static
    {
        return $this->state(fn () => [
            'meta' => [
                'confidence' => 'low',
                'review_required' => true,
                'default_rates' => [
                    'person_days_per_ha' => 5,
                    'machine_days_per_ha' => 5,
                    'days_per_ha' => 5,
                    'reason' => 'sin_historico',
                ],
            ],
        ]);
    }
}
