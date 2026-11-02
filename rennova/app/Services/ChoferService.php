<?php

namespace App\Services;

use App\Models\Chofer;
use App\Models\Cliente;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ChoferService
{
    public function listarClientes()
    {
        return Cliente::orderBy('razon_social')->get();
    }

    public function listar(?string $busqueda = null): LengthAwarePaginator
    {
        $query = Chofer::with('cliente');

        if ($busqueda) {
            $busq = $busqueda;
            $query->where(function ($q) use ($busq) {
                $q->where('apellido', 'ILIKE', "%{$busq}%")
                    ->orWhere('nombre', 'ILIKE', "%{$busq}%")
                    ->orWhere('dni', 'ILIKE', "%{$busq}%")
                    ->orWhere('telefono', 'ILIKE', "%{$busq}%")
                    ->orWhere('direccion', 'ILIKE', "%{$busq}%")
                    ->orWhereHas('cliente', function ($qr) use ($busq) {
                        $qr->where('razon_social', 'ILIKE', "%{$busq}%");
                    });
            });
        }

        return $query->orderByDesc('id_chofer')->paginate(15);
    }

    public function reglas(?int $id = null): array
    {
        $idStr = $id ?? 'NULL';

        return [
            'id_cliente' => 'required|exists:clientes,id_cliente',
            'apellido' => 'required|string|max:100',
            'nombre' => 'required|string|max:100',
            'dni' => 'required|string|max:20|unique:choferes,dni,'.$idStr.',id_chofer',
            'telefono' => 'nullable|string|max:30',
            'direccion' => 'nullable|string|max:150',
            'estado' => 'boolean',
        ];
    }

    /**
     * @throws ValidationException
     */
    public function crearOActualizar(?int $id, array $datos): Chofer
    {
        $validator = Validator::make($datos, $this->reglas($id));

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return Chofer::updateOrCreate(
            ['id_chofer' => $id],
            [
                'id_cliente' => $datos['id_cliente'],
                'apellido' => $datos['apellido'],
                'nombre' => $datos['nombre'],
                'dni' => $datos['dni'],
                'telefono' => $datos['telefono'] ?? null,
                'direccion' => $datos['direccion'] ?? null,
                'estado' => (bool) $datos['estado'],
            ]
        );
    }

    public function obtener(int $id): Chofer
    {
        return Chofer::findOrFail($id);
    }

    public function eliminar(int $id): void
    {
        Chofer::findOrFail($id)->delete();
    }
}
