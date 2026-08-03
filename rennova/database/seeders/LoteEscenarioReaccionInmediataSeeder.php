<?php

namespace Database\Seeders;

use App\Models\Lote;
use Illuminate\Database\Seeder;

class LoteEscenarioReaccionInmediataSeeder extends Seeder
{
    public function run(): void
    {
        Lote::updateOrCreate(
            [
                'propietario' => 'Demo - Reaccion Inmediata',
                'ubicacion' => 'Puerto Iguazú, Misiones',
            ],
            [
                'propietario' => 'Demo - Reaccion Inmediata',
                'ubicacion' => 'Puerto Iguazú, Misiones',
                'estado' => 'activo',
                'condicion_compra' => 'alquilado',
                'especie' => 'Eucalipto',
                'superficie' => 96.7,
                'latitud' => -25.695139,
                'longitud' => -54.436389,
            ]
        );
    }
}
