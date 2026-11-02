<?php

namespace App\Services;

use App\Models\Empleado;
use App\Models\Recibo;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ReciboService
{
    public function listarEmpleados()
    {
        return Empleado::all();
    }

    public function listar(?string $busqueda = null): LengthAwarePaginator
    {
        $query = Recibo::with('empleado');

        if ($busqueda) {
            $busq = $busqueda;
            $query->where(function ($q) use ($busq) {
                $q->whereRaw('CAST(monto AS TEXT) ILIKE ?', ['%'.$busq.'%'])
                    ->orWhereRaw('CAST(monto_bruto AS TEXT) ILIKE ?', ['%'.$busq.'%'])
                    ->orWhereDate('fecha_emision', $busq)
                    ->orWhereHas('empleado', function ($qe) use ($busq) {
                        $qe->where('apellido', 'ILIKE', '%'.$busq.'%')
                            ->orWhere('nombre', 'ILIKE', '%'.$busq.'%');
                    });
            });
        }

        return $query->orderBy('id_recibo', 'desc')->paginate(15);
    }

    public function reglas(): array
    {
        return [
            'id_empleado' => 'required|exists:empleados,id_empleado',
            'fecha_emision' => 'required|date',
            'monto_bruto' => 'required|numeric|min:0',
            'descuentos' => 'nullable|numeric|min:0',
            'observaciones' => 'nullable|string|max:150',
        ];
    }

    /**
     * @throws ValidationException
     */
    public function crearOActualizar(?int $id, array $datos): Recibo
    {
        $validator = Validator::make($datos, $this->reglas());

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $montoBruto = (float) $datos['monto_bruto'];
        $descuentos = (float) ($datos['descuentos'] ?? 0);
        $monto = $montoBruto - $descuentos;

        return Recibo::updateOrCreate(
            ['id_recibo' => $id],
            [
                'id_empleado' => $datos['id_empleado'],
                'fecha_emision' => $datos['fecha_emision'],
                'monto_bruto' => $montoBruto,
                'descuentos' => $descuentos,
                'monto' => $monto,
                'observaciones' => $datos['observaciones'] ?? null,
            ]
        );
    }

    public function obtener(int $id): Recibo
    {
        return Recibo::findOrFail($id);
    }

    public function eliminar(int $id): void
    {
        Recibo::findOrFail($id)->delete();
    }
}
