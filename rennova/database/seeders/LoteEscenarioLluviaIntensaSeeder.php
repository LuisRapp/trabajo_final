<?php

namespace Database\Seeders;

use App\Models\Lote;
use Illuminate\Database\Seeder;

class LoteEscenarioLluviaIntensaSeeder extends Seeder
{
    public function run(): void
    {
        Lote::updateOrCreate(
            [
                'propietario' => 'Demo - Lluvia Intensa',
                'ubicacion' => 'San Pedro, Misiones',
            ],
            [
                'propietario' => 'Demo - Lluvia Intensa',
                'ubicacion' => 'San Pedro, Misiones',
                'estado' => 'activo',
                'condicion_compra' => 'propio',
                'especie' => 'Pino',
                'superficie' => 110.4,
                'latitud' => -26.627968,
                'longitud' => -54.108190,
            ]
        );
    }
}
