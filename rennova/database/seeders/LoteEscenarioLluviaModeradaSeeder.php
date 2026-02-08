<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Lote;

class LoteEscenarioLluviaModeradaSeeder extends Seeder
{
    public function run(): void
    {
        Lote::updateOrCreate(
            [
                'propietario' => 'Demo - Lluvia Moderada',
                'ubicacion' => 'Apóstoles, Misiones',
            ],
            [
                'propietario' => 'Demo - Lluvia Moderada',
                'ubicacion' => 'Apóstoles, Misiones',
                'estado' => 'activo',
                'condicion_compra' => 'propio',
                'especie' => 'Eucalipto',
                'superficie' => 85.2,
                'latitud' => -27.918055,
                'longitud' => -55.753333,
            ]
        );
    }
}
