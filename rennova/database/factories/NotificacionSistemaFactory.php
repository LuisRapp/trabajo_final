<?php

namespace Database\Factories;

use App\Models\Mantenimiento;
use App\Models\NotificacionSistema;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;

class NotificacionSistemaFactory extends Factory
{
    protected $model = NotificacionSistema::class;

    public function definition(): array
    {
        return [
            'user_id' => Usuario::factory(),
            'mantenimiento_id' => Mantenimiento::factory(),
            'tipo' => $this->faker->randomElement(['umbral_alcanzado', 'stock_insuficiente', 'recordatorio_programado', 'mantenimiento_vencido']),
            'titulo' => $this->faker->sentence(4),
            'mensaje' => $this->faker->paragraph(),
            'fecha_limite' => $this->faker->dateTimeBetween('now', '+7 days')->format('Y-m-d'),
            'leida' => false,
            'accionada' => false,
        ];
    }

    public function leida(): static
    {
        return $this->state(fn () => [
            'leida' => true,
            'leida_at' => now(),
        ]);
    }

    public function noLeida(): static
    {
        return $this->state(fn () => ['leida' => false, 'leida_at' => null]);
    }

    public function accionada(): static
    {
        return $this->state(fn () => [
            'accionada' => true,
            'accionada_at' => now(),
        ]);
    }

    public function noAccionada(): static
    {
        return $this->state(fn () => ['accionada' => false, 'accionada_at' => null]);
    }

    public function umbralAlcanzado(): static
    {
        return $this->state(fn () => ['tipo' => 'umbral_alcanzado']);
    }
}
