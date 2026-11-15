<?php

namespace App\Services;

use App\Models\RolLaboral;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class RolLaboralService
{
    public function listar(?string $busqueda = null): LengthAwarePaginator
    {
        $query = RolLaboral::query();

        if ($busqueda) {
            $query->where('nombre', 'ILIKE', '%'.$busqueda.'%');
        }

        return $query->orderBy('id_rol_laboral', 'desc')->paginate(15);
    }

    public function reglas(?int $id = null): array
    {
        return [
            'nombre' => $id
                ? 'required|min:3|unique:roles_laborales,nombre,'.$id.',id_rol_laboral'
                : 'required|min:3|unique:roles_laborales,nombre',
        ];
    }

    /**
     * @throws ValidationException
     */
    public function crearOActualizar(?int $id, array $datos): RolLaboral
    {
        $validator = Validator::make($datos, $this->reglas($id));

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return RolLaboral::updateOrCreate(
            ['id_rol_laboral' => $id],
            ['nombre' => $datos['nombre']]
        );
    }

    public function obtener(int $id): RolLaboral
    {
        return RolLaboral::findOrFail($id);
    }

    public function eliminar(int $id): void
    {
        RolLaboral::findOrFail($id)->delete();
    }
}
