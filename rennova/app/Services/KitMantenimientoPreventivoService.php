<?php

namespace App\Services;

use App\Models\Insumo;
use App\Models\KitMantenimientoPreventivo;
use App\Models\Maquinaria;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as ColeccionBase;
use Illuminate\Support\Facades\DB;

/**
 * Acceso a datos de los kits de mantenimiento preventivo.
 * Los componentes Livewire solo interactuan con este servicio: sin consultas directas.
 */
class KitMantenimientoPreventivoService
{
    /**
     * Kits agrupados por maquinaria, con los conteos que muestra el listado.
     */
    public function listarPorMaquinaria(): ColeccionBase
    {
        return KitMantenimientoPreventivo::with(['insumo', 'maquinaria.tipoMaquinaria'])
            ->orderBy('created_at')
            ->get()
            ->groupBy('id_maquinaria')
            ->map(fn (ColeccionBase $items): array => [
                'maquinaria' => $items->first()->maquinaria,
                'items' => $items,
                'total_items' => $items->count(),
                'obligatorios' => $items->where('es_obligatorio', true)->count(),
                'opcionales' => $items->where('es_obligatorio', false)->count(),
            ]);
    }

    public function itemsDeMaquinaria(?int $idMaquinaria): Collection
    {
        if (! $idMaquinaria) {
            return new Collection;
        }

        return KitMantenimientoPreventivo::with('insumo')
            ->where('id_maquinaria', $idMaquinaria)
            ->orderBy('es_obligatorio', 'desc')
            ->orderBy('created_at')
            ->get();
    }

    public function maquinarias(): Collection
    {
        return Maquinaria::with('tipoMaquinaria')->orderBy('modelo')->get();
    }

    public function insumos(): Collection
    {
        return Insumo::orderBy('nombre')->get();
    }

    public function obtenerMaquinaria(?int $id): ?Maquinaria
    {
        return $id ? Maquinaria::with('tipoMaquinaria')->find($id) : null;
    }

    public function obtenerItem(int $id): KitMantenimientoPreventivo
    {
        return KitMantenimientoPreventivo::findOrFail($id);
    }

    public function existeInsumoEnKit(int $idMaquinaria, int $idInsumo, ?int $ignorarId = null): bool
    {
        return KitMantenimientoPreventivo::where('id_maquinaria', $idMaquinaria)
            ->where('id_insumo', $idInsumo)
            ->when($ignorarId, fn ($consulta) => $consulta->where('id_kit', '!=', $ignorarId))
            ->exists();
    }

    public function agregar(array $datos): KitMantenimientoPreventivo
    {
        return KitMantenimientoPreventivo::create($datos);
    }

    public function actualizar(int $id, array $datos): void
    {
        KitMantenimientoPreventivo::where('id_kit', $id)->update($datos);
    }

    public function eliminarItem(int $id): void
    {
        KitMantenimientoPreventivo::findOrFail($id)->delete();
    }

    public function restaurarItem(int $id): bool
    {
        $item = KitMantenimientoPreventivo::withTrashed()->findOrFail($id);

        if (! $item->trashed()) {
            return false;
        }

        $item->restore();

        return true;
    }

    /**
     * Elimina el kit completo de una maquinaria dentro de una transaccion.
     */
    public function eliminarKit(int $idMaquinaria): void
    {
        DB::transaction(function () use ($idMaquinaria): void {
            KitMantenimientoPreventivo::where('id_maquinaria', $idMaquinaria)->delete();
        });
    }
}
