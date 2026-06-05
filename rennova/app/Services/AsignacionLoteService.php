<?php

namespace App\Services;

use App\Models\Empleado;
use App\Models\Lote;
use App\Models\Maquinaria;
use Illuminate\Support\Facades\DB;
use OwenIt\Auditing\Models\Audit;

class AsignacionLoteService
{
    /**
     * Assign employees and machinery to a lot.
     *
     * Synchronizes the many-to-many relationships and registers audit
     * records for attached and detached resources, all within a transaction.
     *
     * @param  int  $loteId  The lot ID to assign resources to
     * @param  array<int>  $empleadosIds  Employee IDs to assign
     * @param  array<int>  $maquinariasIds  Machinery IDs to assign
     * @param  array  $requestData  Request context: user_id, ip_address, user_agent, url
     * @return array{empleados_adjuntados: array<int>, empleados_desvinculados: array<int>, maquinarias_adjuntadas: array<int>, maquinarias_desvinculadas: array<int>}
     */
    public function asignarRecursos(int $loteId, array $empleadosIds, array $maquinariasIds, array $requestData): array
    {
        return DB::transaction(function () use ($loteId, $empleadosIds, $maquinariasIds, $requestData) {
            $lote = Lote::findOrFail($loteId);

            $empleadosActuales = $lote->empleados()->pluck('empleados.id_empleado')->toArray();
            $maquinariasActuales = $lote->maquinarias()->pluck('maquinarias.id_maquinaria')->toArray();

            $empleadosAdjuntar = array_values(array_diff($empleadosIds, $empleadosActuales));
            $empleadosDesvincular = array_values(array_diff($empleadosActuales, $empleadosIds));

            $maquinariasAdjuntar = array_values(array_diff($maquinariasIds, $maquinariasActuales));
            $maquinariasDesvincular = array_values(array_diff($maquinariasActuales, $maquinariasIds));

            $lote->empleados()->sync($empleadosIds);
            $lote->maquinarias()->sync($maquinariasIds);

            $this->registrarAuditoriaAdjuntos($lote, 'empleados', $empleadosAdjuntar, $requestData);
            $this->registrarAuditoriaAdjuntos($lote, 'maquinarias', $maquinariasAdjuntar, $requestData);
            $this->registrarAuditoriaDesvinculados($lote, 'empleados', $empleadosDesvincular, $requestData);
            $this->registrarAuditoriaDesvinculados($lote, 'maquinarias', $maquinariasDesvincular, $requestData);

            return [
                'empleados_adjuntados' => $empleadosAdjuntar,
                'empleados_desvinculados' => $empleadosDesvincular,
                'maquinarias_adjuntadas' => $maquinariasAdjuntar,
                'maquinarias_desvinculadas' => $maquinariasDesvincular,
            ];
        });
    }

    /**
     * Remove all resource assignments from a lot.
     *
     * Detaches all employees and machinery, recording audit entries.
     *
     * @param  int  $loteId  The lot ID to clear assignments from
     * @param  array  $requestData  Request context: user_id, ip_address, user_agent, url
     */
    public function eliminarAsignaciones(int $loteId, array $requestData): void
    {
        DB::transaction(function () use ($loteId, $requestData) {
            $lote = Lote::findOrFail($loteId);

            $empleadosActuales = $lote->empleados()->pluck('empleados.id_empleado')->toArray();
            $maquinariasActuales = $lote->maquinarias()->pluck('maquinarias.id_maquinaria')->toArray();

            $lote->empleados()->detach();
            $lote->maquinarias()->detach();

            $this->registrarAuditoriaDesvinculados($lote, 'empleados', $empleadosActuales, $requestData);
            $this->registrarAuditoriaDesvinculados($lote, 'maquinarias', $maquinariasActuales, $requestData);
        });
    }

    /**
     * Mark a lot as finished and release its resources.
     *
     * Sets estado='terminado', detaches all employees and machinery,
     * and records audit entries, all within a transaction.
     *
     * @param  int  $loteId  The lot ID to liberate
     * @param  array  $requestData  Request context: user_id, ip_address, user_agent, url
     */
    public function liberarRecursos(int $loteId, array $requestData): void
    {
        DB::transaction(function () use ($loteId, $requestData) {
            $lote = Lote::findOrFail($loteId);

            $empleadosActuales = $lote->empleados()->pluck('empleados.id_empleado')->toArray();
            $maquinariasActuales = $lote->maquinarias()->pluck('maquinarias.id_maquinaria')->toArray();

            $lote->estado = 'terminado';
            $lote->save();

            $lote->empleados()->detach();
            $lote->maquinarias()->detach();

            $this->registrarAuditoriaDesvinculados($lote, 'empleados', $empleadosActuales, $requestData);
            $this->registrarAuditoriaDesvinculados($lote, 'maquinarias', $maquinariasActuales, $requestData);
        });
    }

    /**
     * Register audit records for attached resources.
     */
    private function registrarAuditoriaAdjuntos(Lote $lote, string $relacion, array $ids, array $requestData): void
    {
        if (empty($ids)) {
            return;
        }

        if ($relacion === 'empleados') {
            $detalle = Empleado::whereIn('id_empleado', $ids)
                ->get(['id_empleado', 'apellido', 'nombre'])
                ->map(fn ($e) => "{$e->apellido}, {$e->nombre} (ID: {$e->id_empleado})")
                ->toArray();
        } else {
            $detalle = Maquinaria::whereIn('id_maquinaria', $ids)
                ->get(['id_maquinaria', 'modelo'])
                ->map(fn ($m) => "{$m->modelo} (ID: {$m->id_maquinaria})")
                ->toArray();
        }

        Audit::create([
            'auditable_type' => Lote::class,
            'auditable_id' => $lote->id_lote,
            'event' => 'attached',
            'old_values' => [],
            'new_values' => [
                'relation' => $relacion,
                $relacion.'_ids' => $ids,
                $relacion => $detalle,
            ],
            'user_id' => $requestData['user_id'],
            'ip_address' => $requestData['ip_address'],
            'user_agent' => $requestData['user_agent'],
            'url' => $requestData['url'],
        ]);
    }

    /**
     * Register audit records for detached resources.
     */
    private function registrarAuditoriaDesvinculados(Lote $lote, string $relacion, array $ids, array $requestData): void
    {
        if (empty($ids)) {
            return;
        }

        if ($relacion === 'empleados') {
            $detalle = Empleado::whereIn('id_empleado', $ids)
                ->get(['id_empleado', 'apellido', 'nombre'])
                ->map(fn ($e) => "{$e->apellido}, {$e->nombre} (ID: {$e->id_empleado})")
                ->toArray();
        } else {
            $detalle = Maquinaria::whereIn('id_maquinaria', $ids)
                ->get(['id_maquinaria', 'modelo'])
                ->map(fn ($m) => "{$m->modelo} (ID: {$m->id_maquinaria})")
                ->toArray();
        }

        Audit::create([
            'auditable_type' => Lote::class,
            'auditable_id' => $lote->id_lote,
            'event' => 'detached',
            'old_values' => [
                'relation' => $relacion,
                $relacion.'_ids' => $ids,
                $relacion => $detalle,
            ],
            'new_values' => [],
            'user_id' => $requestData['user_id'],
            'ip_address' => $requestData['ip_address'],
            'user_agent' => $requestData['user_agent'],
            'url' => $requestData['url'],
        ]);
    }
}
