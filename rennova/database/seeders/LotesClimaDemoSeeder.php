<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Lote;

class LotesClimaDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $lotes = [
            [
                'propietario' => 'Demo - Posadas',
                'ubicacion' => 'Posadas, Misiones',
                'estado' => 'activo',
                'condicion_compra' => 'propio',
                'especie' => 'Pino',
                'superficie' => 92.3,
                'latitud' => -27.367794,
                'longitud' => -55.896108,
            ],
            [
                'propietario' => 'Demo - Puerto Iguazú',
                'ubicacion' => 'Puerto Iguazú, Misiones',
                'estado' => 'activo',
                'condicion_compra' => 'alquilado',
                'especie' => 'Eucalipto',
                'superficie' => 104.6,
                'latitud' => -25.695139,
                'longitud' => -54.436389,
            ],
            [
                'propietario' => 'Demo - Oberá',
                'ubicacion' => 'Oberá, Misiones',
                'estado' => 'activo',
                'condicion_compra' => 'propio',
                'especie' => 'Pino',
                'superficie' => 88.4,
                'latitud' => -27.487155,
                'longitud' => -55.119354,
            ],
            [
                'propietario' => 'Demo - Eldorado',
                'ubicacion' => 'Eldorado, Misiones',
                'estado' => 'activo',
                'condicion_compra' => 'propio',
                'especie' => 'Eucalipto',
                'superficie' => 97.1,
                'latitud' => -26.408999,
                'longitud' => -54.694915,
            ],
            [
                'propietario' => 'Demo - San Vicente',
                'ubicacion' => 'San Vicente, Misiones',
                'estado' => 'activo',
                'condicion_compra' => 'alquilado',
                'especie' => 'Pino',
                'superficie' => 76.8,
                'latitud' => -26.616079,
                'longitud' => -54.133743,
            ],
            [
                'propietario' => 'Demo - Gobernador Virasoro',
                'ubicacion' => 'Gob. Virasoro, Corrientes',
                'estado' => 'activo',
                'condicion_compra' => 'propio',
                'especie' => 'Pino',
                'superficie' => 120.9,
                'latitud' => -28.050195,
                'longitud' => -56.030444,
            ],
            [
                'propietario' => 'Demo - Santo Tomé',
                'ubicacion' => 'Santo Tomé, Corrientes',
                'estado' => 'activo',
                'condicion_compra' => 'propio',
                'especie' => 'Eucalipto',
                'superficie' => 115.4,
                'latitud' => -28.549328,
                'longitud' => -56.050347,
            ],
            [
                'propietario' => 'Demo - Lluvia Intensa (Misiones)',
                'ubicacion' => 'San Pedro, Misiones',
                'estado' => 'activo',
                'condicion_compra' => 'propio',
                'especie' => 'Pino',
                'superficie' => 102.2,
                'latitud' => -26.627968,
                'longitud' => -54.108190,
            ],
            [
                'propietario' => 'Demo - Lluvia Nocturna (Corrientes Norte)',
                'ubicacion' => 'Ituzaingó, Corrientes',
                'estado' => 'activo',
                'condicion_compra' => 'alquilado',
                'especie' => 'Eucalipto',
                'superficie' => 98.6,
                'latitud' => -27.581005,
                'longitud' => -56.689445,
            ],
            [
                'propietario' => 'Demo - Mantenimiento Preventivo',
                'ubicacion' => 'Jardín América, Misiones',
                'estado' => 'activo',
                'condicion_compra' => 'propio',
                'especie' => 'Pino',
                'superficie' => 95.7,
                'latitud' => -27.041149,
                'longitud' => -55.227308,
            ],
        ];

        foreach ($lotes as $lote) {
            Lote::updateOrCreate(
                [
                    'propietario' => $lote['propietario'],
                    'ubicacion' => $lote['ubicacion'],
                ],
                $lote
            );
        }

        $this->command->info('✅ Lotes demo de clima creados/actualizados.');
    }
}
