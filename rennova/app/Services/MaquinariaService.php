<?php

namespace App\Services;

use App\Models\Maquinaria;
use App\Models\TipoMaquinaria;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class MaquinariaService
{
    public function listarTipos()
    {
        return TipoMaquinaria::all();
    }

    public function listar(?string $busqueda = null): LengthAwarePaginator
    {
        $query = Maquinaria::with('tipoMaquinaria');

        if ($busqueda) {
            $busq = $busqueda;
            $query->where(function ($q) use ($busq) {
                $q->where('modelo', 'ILIKE', '%'.$busq.'%')
                    ->orWhere('estado', 'ILIKE', '%'.$busq.'%')
                    ->orWhereHas('tipoMaquinaria', function ($qt) use ($busq) {
                        $qt->where('nombre', 'ILIKE', '%'.$busq.'%');
                    });
            });
        }

        return $query->orderBy('id_maquinaria', 'desc')->paginate(15);
    }

    public function reglas(): array
    {
        return [
            'id_tipo_maquinaria' => 'required|exists:tipo_maquinarias,id_tipo_maquinaria',
            'modelo' => 'required|min:2',
            'estado' => 'required|in:disponible,asignada,en_mantenimiento',
            'es_alquilada' => 'required|boolean',
            'fecha_inicio_actividades' => 'required|date',
            'umbral_toneladas' => 'nullable|numeric|min:0',
        ];
    }

    /**
     * @throws ValidationException
     */
    public function crearOActualizar(?int $id, array $datos): Maquinaria
    {
        $validator = Validator::make($datos, $this->reglas());

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return Maquinaria::updateOrCreate(
            ['id_maquinaria' => $id],
            [
                'id_tipo_maquinaria' => $datos['id_tipo_maquinaria'],
                'modelo' => $datos['modelo'],
                'estado' => $datos['estado'],
                'es_alquilada' => $datos['es_alquilada'],
                'fecha_inicio_actividades' => $datos['fecha_inicio_actividades'],
                'umbral_toneladas' => $datos['umbral_toneladas'] ?? null,
            ]
        );
    }

    public function obtener(int $id): Maquinaria
    {
        return Maquinaria::findOrFail($id);
    }

    public function eliminar(int $id): void
    {
        Maquinaria::findOrFail($id)->delete();
    }
}
