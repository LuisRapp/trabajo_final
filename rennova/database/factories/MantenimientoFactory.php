<?php

namespace Database\Factories;

use App\Models\Mantenimiento;
use App\Models\Maquinaria;
use App\Models\TipoMantenimiento;
use Illuminate\Database\Eloquent\Factories\Factory;

class MantenimientoFactory extends Factory
{
    protected $model = Mantenimiento::class;

    public function definition(): array
    {
        return [
            'id_maquinaria' => Maquinaria::factory(),
            'id_tipo_mantenimiento' => TipoMantenimiento::factory(),
            'fecha_inicio' => $this->faker->dateTimeBetween('-1 month', 'now')->format('Y-m-d'),
            'fecha_programada' => $this->faker->dateTimeBetween('now', '+1 month')->format('Y-m-d'),
            'estado' => 'programado',
            'costo_total' => null,
            'fecha_fin' => null,
            'toneladas_snapshot' => null,
            'costo_mano_obra' => null,
        ];
    }

    public function programado(): static
    {
        return $this->state(fn () => ['estado' => 'programado']);
    }

    public function enCurso(): static
    {
        return $this->state(fn () => ['estado' => 'en curso']);
    }

    public function completado(): static
    {
        return $this->state(fn () => [
            'estado' => 'completado',
            'fecha_fin' => $this->faker->dateTimeBetween('-1 week', 'now')->format('Y-m-d'),
            'costo_total' => $this->faker->randomFloat(2, 100, 5000),
        ]);
    }

    public function vencido(): static
    {
        return $this->state(fn () => [
            'estado' => 'vencido',
            'fecha_programada' => $this->faker->dateTimeBetween('-2 months', '-1 day')->format('Y-m-d'),
        ]);
    }
}
