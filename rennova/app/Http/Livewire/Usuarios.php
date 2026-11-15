<?php

namespace App\Http\Livewire;

use App\Services\UsuarioService;
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

    protected UsuarioService $servicio;

    public function boot(): void
    {
        $this->servicio = app(UsuarioService::class);
    }

    protected function rules()
    {
        return $this->servicio->reglas($this->usuario_id);
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
            'usuarios' => $this->servicio->listar($this->busqueda),
        ]);
    }

    public function updatedBusqueda()
    {
        $this->resetPage();
    }

    public function guardar()
    {
        $this->validate();

        $this->servicio->crearOActualizar($this->usuario_id, [
            'nombre' => $this->nombre,
            'apellido' => $this->apellido,
            'email' => $this->email,
            'telefono' => $this->telefono,
            'password' => $this->password,
            'password_confirmation' => $this->password_confirmation,
            'activo' => $this->activo,
        ]);

        session()->flash('message', $this->usuario_id ? 'Usuario actualizado correctamente.' : 'Usuario creado correctamente.');
        $this->tab_activo = 'listado';
        $this->resetCampos();
        $this->dispatch('usuarioGuardado');
    }

    public function editar($id)
    {
        $this->tab_activo = 'nuevo';
        $usuario = $this->servicio->obtener($id);
        $this->usuario_id = $usuario->id;
        $this->nombre = $usuario->nombre;
        $this->apellido = $usuario->apellido;
        $this->email = $usuario->email;
        $this->telefono = $usuario->telefono;
        $this->activo = $usuario->trashed() ? 0 : 1;
    }

    public function eliminar($id)
    {
        $this->servicio->eliminar($id);
        session()->flash('message', 'Usuario eliminado correctamente.');
    }

    public function resetCampos()
    {
        $this->reset(['usuario_id', 'nombre', 'apellido', 'email', 'password', 'password_confirmation', 'telefono', 'activo']);
    }
}
