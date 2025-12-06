<?php

namespace Database\Factories;

use App\Models\Insumo;
use App\Models\Proveedor;
use App\Models\UnidadMedida;
use Illuminate\Database\Eloquent\Factories\Factory;

class InsumoFactory extends Factory
{
    protected $model = Insumo::class;

    public function definition(): array
    {
        return [
            'nombre' => $this->faker->randomElement([
                'Aceite hidráulico',
                'Filtro de aire',
                'Cadena de sierra',
                'Repuesto de oruga',
                'Lubricante cadena',
                'Diesel grado 3',
                'Grasa multipropósito',
            ]),
            'descripcion' => $this->faker->sentence(6),
            'id_unidad_medida' => UnidadMedida::factory(),
            'id_proveedor' => $this->faker->boolean(75) ? Proveedor::factory() : null,
            'costo_unitario' => $this->faker->randomFloat(2, 50, 1200),
        ];
    }
}
