<?php

namespace App\Services;

use App\Models\Usuario;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class UsuarioService
{
    public function listar(?string $busqueda = null): LengthAwarePaginator
    {
        $query = Usuario::withTrashed();

        if ($busqueda) {
            $busq = $busqueda;
            $query->where(function ($q) use ($busq) {
                $q->where('nombre', 'ILIKE', '%'.$busq.'%')
                    ->orWhere('apellido', 'ILIKE', '%'.$busq.'%')
                    ->orWhere('email', 'ILIKE', '%'.$busq.'%')
                    ->orWhere('telefono', 'ILIKE', '%'.$busq.'%');
            });
        }

        return $query->orderBy('id', 'desc')->paginate(15);
    }

    public function reglas(?int $id = null): array
    {
        return [
            'nombre' => 'required|min:2',
            'apellido' => 'required|min:2',
            'email' => $id
                ? 'required|email|unique:usuarios,email,'.$id.',id'
                : 'required|email|unique:usuarios,email',
            'password' => $id ? 'nullable|min:6|confirmed' : 'required|min:6|confirmed',
            'telefono' => 'nullable|string',
            'activo' => 'required|boolean',
        ];
    }

    /**
     * @throws ValidationException
     */
    public function crearOActualizar(?int $id, array $datos): Usuario
    {
        $validator = Validator::make($datos, $this->reglas($id));

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $data = [
            'nombre' => $datos['nombre'],
            'apellido' => $datos['apellido'],
            'email' => $datos['email'],
            'telefono' => $datos['telefono'] ?? null,
        ];

        if (! empty($datos['password'])) {
            $data['password'] = Hash::make($datos['password']);
        }

        $usuario = Usuario::updateOrCreate(['id' => $id], $data);

        if ((int) ($datos['activo'] ?? 1) === 0 && ! $usuario->trashed()) {
            $usuario->delete();
        } elseif ((int) ($datos['activo'] ?? 1) === 1 && $usuario->trashed()) {
            $usuario->restore();
        }

        return $usuario;
    }

    public function obtener(int $id): Usuario
    {
        return Usuario::findOrFail($id);
    }

    public function eliminar(int $id): void
    {
        Usuario::findOrFail($id)->delete();
    }
}
