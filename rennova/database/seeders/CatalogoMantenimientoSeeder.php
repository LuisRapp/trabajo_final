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
     * @var array<int, string>
     */
    private const TIPOS = ['Preventivo', 'Correctivo'];

    public function run(): void
    {
        foreach (self::TIPOS as $nombre) {
            $tipo = TipoMantenimiento::withTrashed()->firstOrCreate(['nombre' => $nombre]);

            if ($tipo->trashed()) {
                $tipo->restore();
            }
        }
    }
}
