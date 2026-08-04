<?php

namespace Database\Factories;

use App\Models\KitMantenimientoPreventivo;
use App\Models\Maquinaria;
use App\Models\Insumo;
use App\Models\TipoMaquinaria;
use Illuminate\Database\Eloquent\Factories\Factory;

class KitMantenimientoPreventivoFactory extends Factory
{
    protected $model = KitMantenimientoPreventivo::class;

    public function definition(): array
    {
        $tipoMaquinaria = TipoMaquinaria::factory();
        
        return [
            'id_tipo_maquinaria' => $tipoMaquinaria,
            'id_maquinaria' => Maquinaria::factory(['id_tipo_maquinaria' => $tipoMaquinaria]),
            'id_insumo' => Insumo::factory(),
            'cantidad_requerida' => $this->faker->randomFloat(2, 1, 10),
            'es_obligatorio' => $this->faker->boolean(),
        ];
    }

    public function paraMaquinaria($maquinariaId): self
    {
        return $this->state(['id_maquinaria' => $maquinariaId]);
    }
}
