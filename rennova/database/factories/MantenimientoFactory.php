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
            'fecha_inicio' => $this->faker->date(),
            'fecha_programada' => $this->faker->date(),
            'estado' => 'programado',
            'costo_total' => $this->faker->randomFloat(2, 100, 5000),
            'costo_mano_obra' => $this->faker->randomFloat(2, 50, 1000),
        ];
    }

    public function programado(): self
    {
        return $this->state(['estado' => 'programado']);
    }

    public function enCurso(): self
    {
        return $this->state(['estado' => 'en curso']);
    }

    public function completado(): self
    {
        return $this->state(['estado' => 'completado']);
    }

    public function vencido(): self
    {
        return $this->state(['estado' => 'vencido']);
    }
}
