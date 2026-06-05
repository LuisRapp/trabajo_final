<?php

namespace Database\Factories;

use App\Models\HistoricoRolLaboral;
use App\Models\RolLaboral;
use Illuminate\Database\Eloquent\Factories\Factory;

class HistoricoRolLaboralFactory extends Factory
{
    protected $model = HistoricoRolLaboral::class;

    public function definition(): array
    {
        return [
            'rol_laboral_id' => RolLaboral::factory(),
            'jornal_diario' => 1500.00,
            'precio_tonelada' => 100.00,
            'fecha_inicio' => now()->subYear()->toDateString(),
            'fecha_fin' => null,
            'motivo_cambio' => null,
        ];
    }
}
