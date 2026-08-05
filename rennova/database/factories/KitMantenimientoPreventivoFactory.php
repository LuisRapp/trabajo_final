<?php

namespace Database\Factories;

use App\Models\Insumo;
use App\Models\KitMantenimientoPreventivo;
use App\Models\Maquinaria;
use App\Models\TipoMaquinaria;
use Illuminate\Database\Eloquent\Factories\Factory;

class KitMantenimientoPreventivoFactory extends Factory
{
    protected $model = KitMantenimientoPreventivo::class;

    public function definition(): array
    {
        return [
            'id_tipo_maquinaria' => TipoMaquinaria::factory(),
            'id_insumo' => Insumo::factory(),
            'cantidad_requerida' => $this->faker->randomFloat(2, 1, 20),
            'es_obligatorio' => $this->faker->boolean(75),
        ];
    }

    public function paraMaquinaria($maquinaria = null): static
    {
        return $this->state(fn () => [
            'id_maquinaria' => $maquinaria ?? Maquinaria::factory(),
        ]);
    }
}
