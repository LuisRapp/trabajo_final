<?php

namespace App\Services;

use App\Models\Lote;
use App\Models\LoteTarea;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class LoteService
{
    public function listar(?string $busqueda = null): LengthAwarePaginator
    {
        $query = Lote::query();

        if (! empty($busqueda)) {
            $query->where(function ($q) use ($busqueda) {
                $q->where('propietario', 'ILIKE', '%'.$busqueda.'%')
                    ->orWhere('ubicacion', 'ILIKE', '%'.$busqueda.'%')
                    ->orWhere('especie', 'ILIKE', '%'.$busqueda.'%');
            });
        }

        return $query->orderBy('id_lote', 'desc')->paginate(15);
    }

    public function reglas(): array
    {
        return [
            'propietario' => 'required|string|min:3|max:100',
            'ubicacion' => 'required|string|min:3|max:150',
            'especie' => 'required|string|min:2|max:100',
            'superficie' => 'required|numeric|min:0.1|max:10000',
            'condicion_compra' => 'required|in:propio,alquilado',
            'estado' => 'required|in:activo,en_proceso,inactivo,cerrado,baja',
            'main_task_type' => 'required|in:tala_rasa,raleo,poda,limpieza',
            'latitud' => 'nullable|numeric|between:-90,90',
            'longitud' => 'nullable|numeric|between:-180,180',
        ];
    }

    /**
     * Crea o actualiza un lote.
     *
     * @return array{lote: Lote, tareas_activas: ?int}
     *
     * @throws ValidationException
     */
    public function crearOActualizar(?int $id, array $datos): array
    {
        $validator = Validator::make($datos, $this->reglas());

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $campos = Arr::only($datos, ['propietario', 'ubicacion', 'superficie', 'estado', 'condicion_compra', 'especie', 'latitud', 'longitud', 'main_task_type']);
        $tareasActivas = null;

        if ($id) {
            $lote = Lote::findOrFail($id);
            $estadoAnterior = $lote->estado;
            $lote->update($campos);

            if ($estadoAnterior !== 'en_proceso' && $lote->estado === 'en_proceso') {
                $tareasActivas = $this->contarTareasActivas($lote->id_lote);
            }
        } else {
            $lote = Lote::create($campos);

            if ($lote->estado === 'en_proceso') {
                $tareasActivas = 0;
            }
        }

        return ['lote' => $lote, 'tareas_activas' => $tareasActivas];
    }

    private function contarTareasActivas(int $loteId): int
    {
        return LoteTarea::query()
            ->where('id_lote', $loteId)
            ->whereIn('estado', ['planificada', 'en_ejecucion'])
            ->count();
    }

    public function obtener(int $id): Lote
    {
        return Lote::findOrFail($id);
    }

    public function eliminar(int $id): void
    {
        Lote::destroy($id);
    }
}
