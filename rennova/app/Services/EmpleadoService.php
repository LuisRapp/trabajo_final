<?php

namespace App\Services;

use App\Models\Empleado;
use App\Models\RolLaboral;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class EmpleadoService
{
    public function listarRoles()
    {
        return RolLaboral::all();
    }

    public function listar(?string $busqueda = null): LengthAwarePaginator
    {
        $query = Empleado::with('rolLaboral');

        if ($busqueda) {
            $busq = $busqueda;
            $query->where(function ($q) use ($busq) {
                $q->where('apellido', 'ILIKE', '%'.$busq.'%')
                    ->orWhere('nombre', 'ILIKE', '%'.$busq.'%')
                    ->orWhereRaw('CAST(dni AS TEXT) ILIKE ?', ['%'.$busq.'%'])
                    ->orWhereHas('rolLaboral', function ($qr) use ($busq) {
                        $qr->where('nombre', 'ILIKE', '%'.$busq.'%');
                    });
            });
        }

        return $query->orderBy('id_empleado', 'desc')->paginate(15);
    }

    public function reglas(?int $id = null): array
    {
        return [
            'id_rol_laboral' => 'required|exists:roles_laborales,id_rol_laboral',
            'dni' => $id
                ? 'nullable|digits:8|unique:empleados,dni,'.$id.',id_empleado'
                : 'required|digits:8|unique:empleados,dni',
            'apellido' => 'required|min:2',
            'nombre' => 'required|min:2',
            'fecha_nacimiento' => 'required|date',
            'fecha_inicio_actividades' => 'required|date',
            'fecha_fin_actividades' => 'nullable|date|after:fecha_inicio_actividades',
        ];
    }

    /**
     * @throws ValidationException
     */
    public function crearOActualizar(?int $id, array $datos): Empleado
    {
        $validator = Validator::make($datos, $this->reglas($id));

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return Empleado::updateOrCreate(
            ['id_empleado' => $id],
            [
                'id_rol_laboral' => $datos['id_rol_laboral'],
                'dni' => $datos['dni'],
                'apellido' => $datos['apellido'],
                'nombre' => $datos['nombre'],
                'fecha_nacimiento' => $datos['fecha_nacimiento'],
                'fecha_inicio_actividades' => $datos['fecha_inicio_actividades'],
                'fecha_fin_actividades' => $datos['fecha_fin_actividades'] ?? null,
            ]
        );
    }

    public function obtener(int $id): Empleado
    {
        return Empleado::findOrFail($id);
    }

    public function eliminar(int $id): void
    {
        Empleado::findOrFail($id)->delete();
    }
}
