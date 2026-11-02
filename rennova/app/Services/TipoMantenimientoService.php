<?php

namespace App\Services;

use App\Models\TipoMantenimiento;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class TipoMantenimientoService
{
    public function listar(?string $busqueda = null): LengthAwarePaginator
    {
        $query = TipoMantenimiento::query();

        if ($busqueda) {
            $query->where('nombre', 'ILIKE', '%'.$busqueda.'%');
        }

        return $query->orderBy('id_tipo_mantenimiento', 'desc')->paginate(15);
    }

    public function reglas(?int $id = null): array
    {
        return [
            'nombre' => $id
                ? 'required|min:3|unique:tipo_mantenimientos,nombre,'.$id.',id_tipo_mantenimiento'
                : 'required|min:3|unique:tipo_mantenimientos,nombre',
        ];
    }

    /**
     * @throws ValidationException
     */
    public function crearOActualizar(?int $id, array $datos): TipoMantenimiento
    {
        $validator = Validator::make($datos, $this->reglas($id));

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return TipoMantenimiento::updateOrCreate(
            ['id_tipo_mantenimiento' => $id],
            ['nombre' => $datos['nombre']]
        );
    }

    public function obtener(int $id): TipoMantenimiento
    {
        return TipoMantenimiento::findOrFail($id);
    }

    public function eliminar(int $id): void
    {
        TipoMantenimiento::findOrFail($id)->delete();
    }
}
