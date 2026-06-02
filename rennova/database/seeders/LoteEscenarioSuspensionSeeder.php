<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Lote;

class LoteEscenarioSuspensionSeeder extends Seeder
{
    public function run(): void
    {
        Lote::updateOrCreate(
            [
                'propietario' => 'Demo - Suspension Total',
                'ubicacion' => 'San Vicente, Misiones',
            ],
            [
                'propietario' => 'Demo - Suspension Total',
                'ubicacion' => 'San Vicente, Misiones',
                'estado' => 'activo',
                'condicion_compra' => 'alquilado',
                'especie' => 'Pino',
                'superficie' => 78.9,
                'latitud' => -26.616079,
                'longitud' => -54.133743,
            ]
        );
    }
}
