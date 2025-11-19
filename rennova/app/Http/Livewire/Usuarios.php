<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class Usuarios extends Component
{
    public $usuarios, $usuario_id, $nombre, $apellido, $email, $password, $password_confirmation, $telefono, $activo, $busqueda = '';

    protected function rules()
    {
        // Construir regla de email dinámicamente
        $emailRules = ['required', 'email'];
        
        // Solo aplicar ignore() si estamos editando (usuario_id tiene valor)
        if ($this->usuario_id) {
            $emailRules[] = Rule::unique('usuarios', 'email')->ignore($this->usuario_id);
        } else {
            $emailRules[] = Rule::unique('usuarios', 'email');
        }

        return [
            'nombre' => 'required|min:2',
            'apellido' => 'required|min:2',
            'email' => $emailRules,
            'password' => $this->usuario_id ? 'nullable|min:6|confirmed' : 'required|min:6|confirmed',
            'telefono' => 'nullable|string',
            'activo' => 'required|boolean',
        ];
    }

    protected $messages = [
        'nombre.required' => 'El nombre es obligatorio.',
        'apellido.required' => 'El apellido es obligatorio.',
        'email.required' => 'El email es obligatorio.',
        'email.email' => 'El email debe ser válido.',
        'email.unique' => 'Este email ya está registrado.',
        'password.required' => 'La contraseña es obligatoria.',
        'password.min' => 'La contraseña debe tener al menos 6 caracteres.',
        'password.confirmed' => 'Las contraseñas no coinciden.',
        'activo.required' => 'Debe indicar si está activo.',
    ];

    public function render()
    {
        $this->cargarUsuarios();
        return view('livewire.usuarios');
    }

    public function cargarUsuarios()
    {
        $query = Usuario::query();

        if ($this->busqueda) {
            $busq = $this->busqueda;
            $query->where(function($q) use ($busq) {
                $q->where('nombre', 'ILIKE', '%' . $busq . '%')
                  ->orWhere('apellido', 'ILIKE', '%' . $busq . '%')
                  ->orWhere('email', 'ILIKE', '%' . $busq . '%')
                  ->orWhere('telefono', 'ILIKE', '%' . $busq . '%');
            });
        }

        $this->usuarios = $query->orderBy('id', 'desc')->get();
    }

    public function updatedBusqueda()
    {
        $this->cargarUsuarios();
    }

    public function guardar()
    {
        $this->validate();

        $data = [
            'nombre' => $this->nombre,
            'apellido' => $this->apellido,
            'email' => $this->email,
            'telefono' => $this->telefono,
            'activo' => $this->activo,
        ];

        if ($this->password) {
            $data['password'] = Hash::make($this->password);
        }

        Usuario::updateOrCreate(
            ['id' => $this->usuario_id],
            $data
        );

        session()->flash('message', $this->usuario_id ? 'Usuario actualizado correctamente.' : 'Usuario creado correctamente.');
        $this->resetCampos();
        $this->dispatch('usuarioGuardado');
    }

    public function editar($id)
    {
        $usuario = Usuario::findOrFail($id);
        $this->usuario_id = $usuario->id;
        $this->nombre = $usuario->nombre;
        $this->apellido = $usuario->apellido;
        $this->email = $usuario->email;
        $this->telefono = $usuario->telefono;
        $this->activo = $usuario->activo;
    }

    public function eliminar($id)
    {
        Usuario::findOrFail($id)->delete();
        session()->flash('message', 'Usuario eliminado correctamente.');
    }

    public function resetCampos()
    {
        $this->reset(['usuario_id', 'nombre', 'apellido', 'email', 'password', 'password_confirmation', 'telefono', 'activo']);
    }
}
