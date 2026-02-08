<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Lote;

class LoteEscenarioNormalSeeder extends Seeder
{
    public function run(): void
    {
        Lote::updateOrCreate(
            [
                'propietario' => 'Demo - Normal (Estable)',
                'ubicacion' => 'Montecarlo, Misiones',
            ],
            [
                'propietario' => 'Demo - Normal (Estable)',
                'ubicacion' => 'Montecarlo, Misiones',
                'estado' => 'activo',
                'condicion_compra' => 'propio',
                'especie' => 'Pino',
                'superficie' => 90.5,
                'latitud' => -26.566667,
                'longitud' => -54.75,
            ]
        );
    }
}
