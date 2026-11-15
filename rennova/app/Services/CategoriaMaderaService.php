<?php

namespace App\Services;

use App\Models\CategoriaMadera;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CategoriaMaderaService
{
    /**
     * Lista las categorías de madera con búsqueda opcional por nombre o descripción.
     */
    public function listar(?string $busqueda = null): LengthAwarePaginator
    {
        $query = CategoriaMadera::query();

        if ($busqueda) {
            $busq = $busqueda;
            $query->where(function ($q) use ($busq) {
                $q->where('nombre', 'ILIKE', '%'.$busq.'%')
                    ->orWhere('descripcion', 'ILIKE', '%'.$busq.'%');
            });
        }

        return $query->orderBy('id_categoria_madera', 'desc')->paginate(15);
    }

    /**
     * Reglas de validación (única fuente para componente y servicio).
     */
    public function reglas(?int $id = null): array
    {
        return [
            'nombre' => $id
                ? 'required|min:3|unique:categoria_maderas,nombre,'.$id.',id_categoria_madera'
                : 'required|min:3|unique:categoria_maderas,nombre',
            'descripcion' => 'nullable|string',
        ];
    }

    /**
     * Crea o actualiza una categoría de madera, validando las reglas críticas en el servicio.
     *
     * @param  array{nombre: string, descripcion: ?string}  $datos
     *
     * @throws ValidationException
     */
    public function crearOActualizar(?int $id, array $datos): CategoriaMadera
    {
        $validator = Validator::make($datos, $this->reglas($id));

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return CategoriaMadera::updateOrCreate(
            ['id_categoria_madera' => $id],
            [
                'nombre' => $datos['nombre'],
                'descripcion' => $datos['descripcion'] ?? null,
            ]
        );
    }

    /**
     * Obtiene una categoría de madera por id.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function obtener(int $id): CategoriaMadera
    {
        return CategoriaMadera::findOrFail($id);
    }

    /**
     * Elimina (baja lógica) una categoría de madera.
     */
    public function eliminar(int $id): void
    {
        CategoriaMadera::findOrFail($id)->delete();
    }
}
