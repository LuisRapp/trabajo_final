<?php

namespace App\Services;

use App\Models\Proveedor;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ProveedorService
{
    public function listar(?string $busqueda = null): LengthAwarePaginator
    {
        $query = Proveedor::query();

        if ($busqueda) {
            $query->where(function ($q) use ($busqueda) {
                $q->where('razon_social', 'ILIKE', '%'.$busqueda.'%')
                    ->orWhere('cuit', 'ILIKE', '%'.$busqueda.'%')
                    ->orWhere('email', 'ILIKE', '%'.$busqueda.'%');
            });
        }

        return $query->orderBy('id_proveedor', 'desc')->paginate(15);
    }

    public function reglas(?int $id = null): array
    {
        return [
            'razon_social' => 'required|min:3',
            'cuit' => $id
                ? 'required|digits:11|unique:proveedores,cuit,'.$id.',id_proveedor'
                : 'required|digits:11|unique:proveedores,cuit',
            'direccion' => 'required',
            'telefono' => 'nullable|string',
            'email' => 'nullable|email',
        ];
    }

    /**
     * @throws ValidationException
     */
    public function crearOActualizar(?int $id, array $datos): Proveedor
    {
        $validator = Validator::make($datos, $this->reglas($id));

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return Proveedor::updateOrCreate(
            ['id_proveedor' => $id],
            [
                'razon_social' => $datos['razon_social'],
                'cuit' => $datos['cuit'],
                'direccion' => $datos['direccion'],
                'telefono' => $datos['telefono'] ?? null,
                'email' => $datos['email'] ?? null,
            ]
        );
    }

    public function obtener(int $id): Proveedor
    {
        return Proveedor::findOrFail($id);
    }

    public function eliminar(int $id): void
    {
        Proveedor::findOrFail($id)->delete();
    }
}
