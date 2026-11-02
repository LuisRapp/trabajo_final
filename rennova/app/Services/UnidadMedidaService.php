<?php

namespace App\Services;

use App\Models\UnidadMedida;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class UnidadMedidaService
{
    public function listar(?string $busqueda = null): LengthAwarePaginator
    {
        $query = UnidadMedida::query();

        if ($busqueda) {
            $busq = $busqueda;
            $query->where(function ($q) use ($busq) {
                $q->where('nombre', 'ILIKE', '%'.$busq.'%')
                    ->orWhere('abreviatura', 'ILIKE', '%'.$busq.'%');
            });
        }

        return $query->orderBy('id_unidad_medida', 'desc')->paginate(15);
    }

    public function reglas(?int $id = null): array
    {
        return [
            'nombre' => $id
                ? 'required|min:2|unique:unidad_medidas,nombre,'.$id.',id_unidad_medida'
                : 'required|min:2|unique:unidad_medidas,nombre',
            'abreviatura' => 'required|max:10',
        ];
    }

    /**
     * @throws ValidationException
     */
    public function crearOActualizar(?int $id, array $datos): UnidadMedida
    {
        $validator = Validator::make($datos, $this->reglas($id));

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return UnidadMedida::updateOrCreate(
            ['id_unidad_medida' => $id],
            ['nombre' => $datos['nombre'], 'abreviatura' => $datos['abreviatura']]
        );
    }

    public function obtener(int $id): UnidadMedida
    {
        return UnidadMedida::findOrFail($id);
    }

    public function eliminar(int $id): void
    {
        UnidadMedida::findOrFail($id)->delete();
    }
}
