<?php

namespace App\Services;

use App\Models\TipoMaquinaria;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class TipoMaquinariaService
{
    public function listar(?string $busqueda = null): LengthAwarePaginator
    {
        $query = TipoMaquinaria::query();

        if ($busqueda) {
            $query->where('nombre', 'ILIKE', '%'.$busqueda.'%');
        }

        return $query->orderBy('id_tipo_maquinaria', 'desc')->paginate(15);
    }

    public function reglas(?int $id = null): array
    {
        return [
            'nombre' => 'required|min:3|unique:tipo_maquinarias,nombre,'.($id ?? 'NULL').',id_tipo_maquinaria',
        ];
    }

    /**
     * @throws ValidationException
     */
    public function crearOActualizar(?int $id, array $datos): TipoMaquinaria
    {
        $validator = Validator::make($datos, $this->reglas($id));

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return TipoMaquinaria::updateOrCreate(
            ['id_tipo_maquinaria' => $id],
            ['nombre' => $datos['nombre']]
        );
    }

    public function obtener(int $id): TipoMaquinaria
    {
        return TipoMaquinaria::findOrFail($id);
    }

    public function eliminar(int $id): void
    {
        TipoMaquinaria::findOrFail($id)->delete();
    }
}
