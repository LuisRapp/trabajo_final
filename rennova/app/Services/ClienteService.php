<?php

namespace App\Services;

use App\Models\Cliente;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ClienteService
{
    public function listar(?string $busqueda = null): LengthAwarePaginator
    {
        $query = Cliente::query();

        if ($busqueda) {
            $query->where(function ($q) use ($busqueda) {
                $q->where('razon_social', 'ILIKE', '%'.$busqueda.'%')
                    ->orWhere('cuit', 'ILIKE', '%'.$busqueda.'%')
                    ->orWhere('contacto', 'ILIKE', '%'.$busqueda.'%');
            });
        }

        return $query->orderBy('id_cliente', 'desc')->paginate(15);
    }

    public function reglas(?int $id = null): array
    {
        return [
            'razon_social' => 'required|min:3',
            'cuit' => $id
                ? 'required|digits:11|unique:clientes,cuit,'.$id.',id_cliente'
                : 'required|digits:11|unique:clientes,cuit',
            'direccion' => 'required',
            'contacto' => 'nullable|string',
        ];
    }

    /**
     * @throws ValidationException
     */
    public function crearOActualizar(?int $id, array $datos): Cliente
    {
        $validator = Validator::make($datos, $this->reglas($id));

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return Cliente::updateOrCreate(
            ['id_cliente' => $id],
            [
                'razon_social' => $datos['razon_social'],
                'cuit' => $datos['cuit'],
                'direccion' => $datos['direccion'],
                'contacto' => $datos['contacto'] ?? null,
            ]
        );
    }

    public function obtener(int $id): Cliente
    {
        return Cliente::findOrFail($id);
    }

    public function eliminar(int $id): void
    {
        Cliente::findOrFail($id)->delete();
    }
}
