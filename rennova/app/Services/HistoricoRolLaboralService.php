<?php

namespace App\Services;

use App\Models\HistoricoRolLaboral;
use App\Models\RolLaboral;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class HistoricoRolLaboralService
{
    public function listarRoles()
    {
        return RolLaboral::orderBy('nombre')->get();
    }

    public function listar(?string $busqueda = null): LengthAwarePaginator
    {
        $query = HistoricoRolLaboral::with('rolLaboral');

        if ($busqueda) {
            $busq = $busqueda;
            $query->where(function ($q) use ($busq) {
                $q->where('motivo_cambio', 'ILIKE', "%{$busq}%")
                    ->orWhereDate('fecha_inicio', $busq)
                    ->orWhereDate('fecha_fin', $busq)
                    ->orWhereHas('rolLaboral', function ($qr) use ($busq) {
                        $qr->where('nombre', 'ILIKE', "%{$busq}%");
                    });
            });
        }

        return $query->orderByDesc('fecha_inicio')->paginate(15);
    }

    public function reglas(): array
    {
        return [
            'rol_laboral_id' => 'required|exists:roles_laborales,id_rol_laboral',
            'precio_tonelada' => 'nullable|numeric|min:0',
            'jornal_diario' => 'nullable|numeric|min:0',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'nullable|date|after:fecha_inicio',
            'motivo_cambio' => 'nullable|string',
        ];
    }

    /**
     * @throws ValidationException
     */
    public function crearOActualizar(?int $id, array $datos): HistoricoRolLaboral
    {
        $validator = Validator::make($datos, $this->reglas());

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return HistoricoRolLaboral::updateOrCreate(
            ['id' => $id],
            [
                'rol_laboral_id' => $datos['rol_laboral_id'],
                'precio_tonelada' => $datos['precio_tonelada'] ?? null,
                'jornal_diario' => $datos['jornal_diario'] ?? null,
                'fecha_inicio' => $datos['fecha_inicio'],
                'fecha_fin' => $datos['fecha_fin'] ?? null,
                'motivo_cambio' => $datos['motivo_cambio'] ?? null,
            ]
        );
    }

    public function obtener(int $id): HistoricoRolLaboral
    {
        return HistoricoRolLaboral::findOrFail($id);
    }

    public function eliminar(int $id): void
    {
        HistoricoRolLaboral::findOrFail($id)->delete();
    }
}
