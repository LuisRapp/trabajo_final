<?php

namespace App\Services;

use App\Models\HistoricoCostosMaquinaria;
use App\Models\Maquinaria;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class HistoricoCostosMaquinariaService
{
    public function listarMaquinarias()
    {
        return Maquinaria::all();
    }

    public function listar(?string $busqueda = null): LengthAwarePaginator
    {
        $query = HistoricoCostosMaquinaria::with('maquinaria');

        if ($busqueda) {
            $busq = $busqueda;
            $query->where(function ($q) use ($busq) {
                $q->whereDate('fecha_inicio_vigencia', $busq)
                    ->orWhereDate('fecha_fin_vigencia', $busq)
                    ->orWhereHas('maquinaria', function ($qr) use ($busq) {
                        $qr->where('modelo', 'ILIKE', "%{$busq}%");
                    });
            });
        }

        return $query->orderBy('id_costo', 'desc')->paginate(15);
    }

    public function reglas(): array
    {
        return [
            'id_maquinaria' => 'required|exists:maquinarias,id_maquinaria',
            'costo_por_tonelada' => 'required|numeric|min:0',
            'fecha_inicio_vigencia' => 'required|date',
            'fecha_fin_vigencia' => 'nullable|date|after:fecha_inicio_vigencia',
        ];
    }

    /**
     * @throws ValidationException
     */
    public function crearOActualizar(?int $id, array $datos): HistoricoCostosMaquinaria
    {
        $validator = Validator::make($datos, $this->reglas());

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return HistoricoCostosMaquinaria::updateOrCreate(
            ['id_costo' => $id],
            [
                'id_maquinaria' => $datos['id_maquinaria'],
                'costo_por_tonelada' => $datos['costo_por_tonelada'],
                'fecha_inicio_vigencia' => $datos['fecha_inicio_vigencia'],
                'fecha_fin_vigencia' => $datos['fecha_fin_vigencia'] ?? null,
            ]
        );
    }

    public function obtener(int $id): HistoricoCostosMaquinaria
    {
        return HistoricoCostosMaquinaria::findOrFail($id);
    }

    public function eliminar(int $id): void
    {
        HistoricoCostosMaquinaria::findOrFail($id)->delete();
    }
}
