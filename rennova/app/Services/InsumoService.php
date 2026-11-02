<?php

namespace App\Services;

use App\Models\Insumo;
use App\Models\Proveedor;
use App\Models\UnidadMedida;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class InsumoService
{
    public function listarUnidades()
    {
        return UnidadMedida::all();
    }

    public function listarProveedores()
    {
        return Proveedor::all();
    }

    public function listar(?string $busqueda = null): LengthAwarePaginator
    {
        $query = InventarioService::queryInsumosConStockYPrecio()
            ->with(['unidadMedida', 'proveedor']);

        if ($busqueda) {
            $busq = $busqueda;
            $query->where(function ($q) use ($busq) {
                $q->where('nombre', 'ILIKE', '%'.$busq.'%')
                    ->orWhere('descripcion', 'ILIKE', '%'.$busq.'%')
                    ->orWhereHas('proveedor', function ($qp) use ($busq) {
                        $qp->where('razon_social', 'ILIKE', '%'.$busq.'%');
                    })
                    ->orWhereHas('unidadMedida', function ($qu) use ($busq) {
                        $qu->where('nombre', 'ILIKE', '%'.$busq.'%')
                            ->orWhere('abreviatura', 'ILIKE', '%'.$busq.'%');
                    });
            });
        }

        return $query->orderBy('id_insumo', 'desc')->paginate(15);
    }

    public function reglas(): array
    {
        return [
            'nombre' => 'required|min:3',
            'descripcion' => 'nullable|string',
            'id_unidad_medida' => 'required|exists:unidad_medidas,id_unidad_medida',
            'id_proveedor' => 'required|exists:proveedores,id_proveedor',
        ];
    }

    /**
     * @throws ValidationException
     */
    public function crearOActualizar(?int $id, array $datos): Insumo
    {
        $validator = Validator::make($datos, $this->reglas());

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return Insumo::updateOrCreate(
            ['id_insumo' => $id],
            [
                'nombre' => $datos['nombre'],
                'descripcion' => $datos['descripcion'] ?? null,
                'id_unidad_medida' => $datos['id_unidad_medida'],
                'id_proveedor' => $datos['id_proveedor'],
            ]
        );
    }

    public function obtener(int $id): Insumo
    {
        return Insumo::findOrFail($id);
    }

    public function eliminar(int $id): void
    {
        Insumo::findOrFail($id)->delete();
    }
}
