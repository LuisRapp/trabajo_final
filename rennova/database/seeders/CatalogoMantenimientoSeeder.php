<?php

namespace Database\Seeders;

use App\Models\TipoMantenimiento;
use Illuminate\Database\Seeder;

/**
 * Catalogo de referencia de tipos de mantenimiento.
 *
 * El proceso automatico (PA-01) necesita un tipo preventivo para tipar las
 * ordenes que genera por umbral, y la aprobacion manual necesita ademas un
 * tipo correctivo. Sin este catalogo el sistema no puede crear ordenes.
 *
 * Es idempotente: no duplica filas y restaura las que estaban dadas de baja.
 */
class CatalogoMantenimientoSeeder extends Seeder
{
    /**
     * @var array<int, array{nombre: string, comportamiento: string}>
     */
    private const TIPOS = [
        ['nombre' => 'Preventivo', 'comportamiento' => 'preventivo'],
        ['nombre' => 'Correctivo', 'comportamiento' => 'correctivo'],
    ];

    public function run(): void
    {
        foreach (self::TIPOS as $tipo) {
            $existente = TipoMantenimiento::withTrashed()
                ->where('comportamiento', $tipo['comportamiento'])
                ->orderBy('id_tipo_mantenimiento')
                ->first();

            if ($existente) {
                if ($existente->trashed()) {
                    $existente->restore();
                }

                continue;
            }

            TipoMantenimiento::create($tipo);
        }
    }
}
