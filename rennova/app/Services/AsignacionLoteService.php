<?php

namespace App\Services;

use App\Models\Empleado;
use App\Models\Lote;
use App\Models\Maquinaria;
use App\Models\PropuestaAsignacion;
use Illuminate\Support\Facades\DB;
use OwenIt\Auditing\Models\Audit;

class AsignacionLoteService
{
    /**
     * Asigna empleados y maquinarias a un lote.
     *
     * Sincroniza las relaciones de muchos a muchos y registra auditoría
     * de recursos adjuntados y desvinculados, todo dentro de una transacción.
     *
     * @param  int  $loteId  Identificador del lote al cual asignar recursos
     * @param  array<int>  $empleadosIds  Identificadores de empleados a asignar
     * @param  array<int>  $maquinariasIds  Identificadores de maquinarias a asignar
     * @param  array  $requestData  Contexto del request: user_id, ip_address, user_agent, url
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
     * Elimina todas las asignaciones de recursos de un lote.
     *
     * Desvincula todos los empleados y maquinarias, registrando auditoría.
     *
     * @param  int  $loteId  Identificador del lote del cual limpiar asignaciones
     * @param  array  $requestData  Contexto del request: user_id, ip_address, user_agent, url
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
     * Finaliza un lote: marca el estado 'cerrado', libera sus recursos
     * (desvincula empleados y maquinarias con registro de auditoría) y
     * cierra las propuestas de asignación del lote.
     *
     * Regla única del dominio para el cierre de lotes: todo cierre pasa por
     * este método, sin importar la pantalla de origen. Si el lote ya está
     * cerrado no opera y devuelve false.
     *
     * @param  int  $loteId  Identificador del lote a finalizar
     * @param  array  $requestData  Contexto del request: user_id, ip_address, user_agent, url
     * @return bool True si el lote se finalizó; false si ya estaba cerrado
     */
    public function finalizar(int $loteId, array $requestData): bool
    {
        return DB::transaction(function () use ($loteId, $requestData) {
            $lote = Lote::findOrFail($loteId);

            if ($lote->estado === 'cerrado') {
                return false;
            }

            $empleadosActuales = $lote->empleados()->pluck('empleados.id_empleado')->toArray();
            $maquinariasActuales = $lote->maquinarias()->pluck('maquinarias.id_maquinaria')->toArray();

            $lote->update(['estado' => 'cerrado']);

            $lote->empleados()->detach();
            $lote->maquinarias()->detach();

            $this->registrarAuditoriaDesvinculados($lote, 'empleados', $empleadosActuales, $requestData);
            $this->registrarAuditoriaDesvinculados($lote, 'maquinarias', $maquinariasActuales, $requestData);

            PropuestaAsignacion::query()
                ->where('id_lote', $loteId)
                ->whereNull('deleted_at')
                ->update(['status' => 'closed']);

            return true;
        });
    }

    /**
     * Registra auditoría de los recursos adjuntados.
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
     * Registra auditoría de los recursos desvinculados.
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
