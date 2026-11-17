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
     * La regla "un recurso no puede estar en dos lotes activos a la vez" se
     * valida acá: es la única puerta de entrada, así que ningún llamador puede
     * saltearla. Los recursos se bloquean con lockForUpdate antes de detectar
     * conflictos para serializar asignaciones concurrentes del mismo recurso.
     *
     * @param  int  $loteId  Identificador del lote al cual asignar recursos
     * @param  array<int>  $empleadosIds  Identificadores de empleados a asignar
     * @param  array<int>  $maquinariasIds  Identificadores de maquinarias a asignar
     * @param  array  $requestData  Contexto del request: user_id, ip_address, user_agent, url
     * @return array{empleados_adjuntados: array<int>, empleados_desvinculados: array<int>, maquinarias_adjuntadas: array<int>, maquinarias_desvinculadas: array<int>}
     *
     * @throws \RuntimeException Si un recurso ya está asignado a otro lote activo
     */
    public function asignarRecursos(int $loteId, array $empleadosIds, array $maquinariasIds, array $requestData): array
    {
        return DB::transaction(function () use ($loteId, $empleadosIds, $maquinariasIds, $requestData) {
            $lote = Lote::findOrFail($loteId);

            $this->bloquearRecursos($empleadosIds, $maquinariasIds);
            $this->validarConflictos($lote->id_lote, $empleadosIds, $maquinariasIds);

            $empleadosActuales = $lote->empleados()->pluck('empleados.id_empleado')->toArray();
            $maquinariasActuales = $lote->maquinarias()->pluck('maquinarias.id_maquinaria')->toArray();

            $empleadosAdjuntar = array_values(array_diff($empleadosIds, $empleadosActuales));
            $empleadosDesvincular = array_values(array_diff($empleadosActuales, $empleadosIds));

            $maquinariasAdjuntar = array_values(array_diff($maquinariasIds, $maquinariasActuales));
            $maquinariasDesvincular = array_values(array_diff($maquinariasActuales, $maquinariasIds));

            $lote->empleados()->sync($empleadosIds);
            $lote->maquinarias()->sync($maquinariasIds);

            // Regla del ciclo de vida: asignar recursos a un lote activo lo
            // pone en proceso. Es el mismo disparador que la confirmación de
            // una propuesta, para que el flujo manual y el automático coincidan.
            if ($lote->estado === 'activo') {
                $lote->update(['estado' => 'en_proceso']);
            }

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

    /**
     * Datos de apoyo del formulario de asignaciones.
     *
     * @return array{lotes: \Illuminate\Database\Eloquent\Collection<int, Lote>, empleados: \Illuminate\Database\Eloquent\Collection<int, Empleado>, maquinarias: \Illuminate\Database\Eloquent\Collection<int, Maquinaria>}
     */
    public function obtenerDatosFormulario(): array
    {
        return [
            'lotes' => Lote::orderBy('id_lote', 'desc')->get(),
            'empleados' => Empleado::with('rolLaboral')
                ->whereNull('fecha_fin_actividades')
                ->orderBy('apellido')
                ->orderBy('nombre')
                ->get(),
            'maquinarias' => Maquinaria::with('tipoMaquinaria')->orderBy('modelo')->get(),
        ];
    }

    /**
     * Recursos ya asignados a un lote, para precargar el formulario.
     *
     * @return array{empleados: array<int>, maquinarias: array<int>}
     */
    public function obtenerAsignaciones(int $loteId): array
    {
        $lote = Lote::with(['empleados:id_empleado', 'maquinarias:id_maquinaria'])->find($loteId);

        if (! $lote) {
            return ['empleados' => [], 'maquinarias' => []];
        }

        return [
            'empleados' => $lote->empleados->pluck('id_empleado')->toArray(),
            'maquinarias' => $lote->maquinarias->pluck('id_maquinaria')->toArray(),
        ];
    }

    /**
     * Lotes con al menos un recurso asignado, para el historial.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Lote>
     */
    public function obtenerHistorial(): \Illuminate\Database\Eloquent\Collection
    {
        return Lote::with(['empleados' => function ($q) {
            $q->select('empleados.id_empleado', 'apellido', 'nombre');
        }, 'maquinarias' => function ($q) {
            $q->select('maquinarias.id_maquinaria', 'modelo');
        }])
            ->has('empleados')
            ->orHas('maquinarias')
            ->orderBy('id_lote', 'desc')
            ->get();
    }

    /**
     * Bloquea las filas de los recursos a asignar para serializar asignaciones
     * concurrentes del mismo empleado o maquinaria.
     *
     * @param  array<int>  $empleadosIds
     * @param  array<int>  $maquinariasIds
     */
    private function bloquearRecursos(array $empleadosIds, array $maquinariasIds): void
    {
        if ($empleadosIds !== []) {
            Empleado::query()
                ->whereIn('id_empleado', $empleadosIds)
                ->orderBy('id_empleado')
                ->lockForUpdate()
                ->get(['id_empleado']);
        }

        if ($maquinariasIds !== []) {
            Maquinaria::query()
                ->whereIn('id_maquinaria', $maquinariasIds)
                ->orderBy('id_maquinaria')
                ->lockForUpdate()
                ->get(['id_maquinaria']);
        }
    }

    /**
     * Aplica la regla de exclusividad de recursos entre lotes activos.
     *
     * @param  array<int>  $empleadosIds
     * @param  array<int>  $maquinariasIds
     *
     * @throws \RuntimeException Si algún recurso ya está en otro lote activo
     */
    private function validarConflictos(int $loteId, array $empleadosIds, array $maquinariasIds): void
    {
        $conflictosEmpleados = $this->detectarConflictosEmpleados($loteId, $empleadosIds);
        $conflictosMaquinarias = $this->detectarConflictosMaquinarias($loteId, $maquinariasIds);

        if (empty($conflictosEmpleados) && empty($conflictosMaquinarias)) {
            return;
        }

        $mensaje = 'No se puede guardar: recursos ya asignados a otros lotes activos.';

        if (! empty($conflictosEmpleados)) {
            $mensaje .= ' Empleados: '.implode(', ', $conflictosEmpleados).'.';
        }

        if (! empty($conflictosMaquinarias)) {
            $mensaje .= ' Maquinarias: '.implode(', ', $conflictosMaquinarias).'.';
        }

        throw new \RuntimeException($mensaje);
    }

    /**
     * Detecta conflictos de empleados ya asignados a otros lotes activos.
     *
     * @return array<string>
     */
    public function detectarConflictosEmpleados(int $loteId, array $empleadosIds): array
    {
        if (empty($empleadosIds)) {
            return [];
        }

        $conflictos = [];
        $lotesConflicto = Lote::where('estado', 'activo')
            ->where('id_lote', '!=', $loteId)
            ->whereHas('empleados', function ($q) use ($empleadosIds) {
                $q->whereIn('empleados.id_empleado', $empleadosIds);
            })
            ->with(['empleados' => function ($q) use ($empleadosIds) {
                $q->whereIn('empleados.id_empleado', $empleadosIds)
                    ->select('empleados.id_empleado', 'apellido', 'nombre');
            }])
            ->get();

        foreach ($lotesConflicto as $lote) {
            foreach ($lote->empleados as $empleado) {
                $conflictos[] = "{$empleado->apellido}, {$empleado->nombre} (ya asignado a Lote #{$lote->id_lote} - {$lote->ubicacion})";
            }
        }

        return $conflictos;
    }

    /**
     * Detecta conflictos de maquinarias ya asignadas a otros lotes activos.
     *
     * @return array<string>
     */
    public function detectarConflictosMaquinarias(int $loteId, array $maquinariasIds): array
    {
        if (empty($maquinariasIds)) {
            return [];
        }

        $conflictos = [];
        $lotesConflicto = Lote::where('estado', 'activo')
            ->where('id_lote', '!=', $loteId)
            ->whereHas('maquinarias', function ($q) use ($maquinariasIds) {
                $q->whereIn('maquinarias.id_maquinaria', $maquinariasIds);
            })
            ->with(['maquinarias' => function ($q) use ($maquinariasIds) {
                $q->whereIn('maquinarias.id_maquinaria', $maquinariasIds)
                    ->select('maquinarias.id_maquinaria', 'modelo');
            }])
            ->get();

        foreach ($lotesConflicto as $lote) {
            foreach ($lote->maquinarias as $maquinaria) {
                $conflictos[] = "{$maquinaria->modelo} (ya asignada a Lote #{$lote->id_lote} - {$lote->ubicacion})";
            }
        }

        return $conflictos;
    }
}
