<?php

namespace App\Http\Livewire\Traits;

use App\Models\HistoricoRolLaboral;

trait JornalLookupTrait
{
    /**
     * Look up the active jornal for an employee on a given date.
     *
     * @param  int|null  $idEmpleado  The employee ID
     * @param  string|null  $fecha  The date to look up (Y-m-d)
     * @param  \Illuminate\Support\Collection  $empleados  Collection of employees with rolLaboral relation
     * @return float|null The daily jornal amount, or null if not found
     */
    public function obtenerJornalEmpleadoParaFecha(?int $idEmpleado, ?string $fecha, $empleados): ?float
    {
        if (! $idEmpleado || ! $fecha) {
            return null;
        }

        $empleado = $empleados->firstWhere('id_empleado', $idEmpleado);
        if (! $empleado || ! $empleado->rolLaboral) {
            return null;
        }

        $rolId = $empleado->rolLaboral->id_rol_laboral ?? $empleado->id_rol_laboral ?? null;
        if (! $rolId) {
            return null;
        }

        $hist = HistoricoRolLaboral::where('rol_laboral_id', $rolId)
            ->vigenteEnFecha($fecha)
            ->first();

        if ($hist) {
            return (float) ($hist->jornal_diario ?? 0);
        }

        // Fallback al valor actual del rol si no hay histórico
        return (float) ($empleado->rolLaboral->jornal_diario ?? 0);
    }
}
