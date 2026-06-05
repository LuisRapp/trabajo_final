<?php

namespace App\Http\Livewire;

use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class Usuarios extends Component
{
    use WithPagination;

    public $usuario_id;

    public $nombre;

    public $apellido;

    public $email;

    public $password;

    public $password_confirmation;

    public $telefono;

    public $activo;

    public $busqueda = '';

    public $tab_activo = 'listado';

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
        return view('livewire.usuarios', [
            'usuarios' => $this->cargarUsuarios(),
        ]);
    }

    public function cargarUsuarios()
    {
        $query = Usuario::withTrashed();

        if ($this->busqueda) {
            $busq = $this->busqueda;
            $query->where(function ($q) use ($busq) {
                $q->where('nombre', 'ILIKE', '%'.$busq.'%')
                    ->orWhere('apellido', 'ILIKE', '%'.$busq.'%')
                    ->orWhere('email', 'ILIKE', '%'.$busq.'%')
                    ->orWhere('telefono', 'ILIKE', '%'.$busq.'%');
            });
        }

        return $query->orderBy('id', 'desc')->paginate(15);
    }

    public function updatedBusqueda()
    {
        $this->resetPage();
    }

    public function guardar()
    {
        $this->validate();

        $data = [
            'nombre' => $this->nombre,
            'apellido' => $this->apellido,
            'email' => $this->email,
            'telefono' => $this->telefono,
        ];

        if ($this->password) {
            $data['password'] = Hash::make($this->password);
        }

        $usuario = Usuario::updateOrCreate(
            ['id' => $this->usuario_id],
            $data
        );

        if ($this->activo == 0 && ! $usuario->trashed()) {
            $usuario->delete();
        } elseif ($this->activo == 1 && $usuario->trashed()) {
            $usuario->restore();
        }

        session()->flash('message', $this->usuario_id ? 'Usuario actualizado correctamente.' : 'Usuario creado correctamente.');
        $this->tab_activo = 'listado';
        $this->resetCampos();
        $this->dispatch('usuarioGuardado');
    }

    public function editar($id)
    {
        $this->tab_activo = 'nuevo';
        $usuario = Usuario::findOrFail($id);
        $this->usuario_id = $usuario->id;
        $this->nombre = $usuario->nombre;
        $this->apellido = $usuario->apellido;
        $this->email = $usuario->email;
        $this->telefono = $usuario->telefono;
        $this->activo = $usuario->trashed() ? 0 : 1;
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
