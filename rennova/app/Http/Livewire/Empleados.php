<?php

namespace App\Http\Livewire;

use App\Services\EmpleadoService;
use Livewire\Component;
use Livewire\WithPagination;

class Empleados extends Component
{
    use WithPagination;

    public $empleado_id;

    public $id_rol_laboral;

    public $dni;

    public $apellido;

    public $nombre;

    public $fecha_nacimiento;

    public $fecha_inicio_actividades;

    public $fecha_fin_actividades;

    public $busqueda = '';

    public $roles;

    public $tab_activo = 'listado';

    protected EmpleadoService $servicio;

    public function boot(): void
    {
        $this->servicio = app(EmpleadoService::class);
    }

    public function rules()
    {
        return $this->servicio->reglas($this->empleado_id);
    }

    protected $messages = [
        'id_rol_laboral.required' => 'Debe seleccionar un rol laboral.',
        'dni.digits' => 'El DNI debe tener 8 dígitos.',
        'dni.unique' => 'Este DNI ya está registrado.',
        'apellido.required' => 'El apellido es obligatorio.',
        'nombre.required' => 'El nombre es obligatorio.',
        'fecha_nacimiento.required' => 'La fecha de nacimiento es obligatoria.',
        'fecha_inicio_actividades.required' => 'La fecha de inicio es obligatoria.',
        'fecha_fin_actividades.after' => 'La fecha de fin debe ser posterior a la fecha de inicio.',
    ];

    public function mount()
    {
        $this->roles = $this->servicio->listarRoles();
    }

    public function render()
    {
        return view('livewire.empleados', [
            'empleados' => $this->servicio->listar($this->busqueda),
        ]);
    }

    public function updatedBusqueda()
    {
        $this->resetPage();
    }

    public function guardar()
    {
        $this->validate();

        $this->servicio->crearOActualizar($this->empleado_id, [
            'id_rol_laboral' => $this->id_rol_laboral,
            'dni' => $this->dni,
            'apellido' => $this->apellido,
            'nombre' => $this->nombre,
            'fecha_nacimiento' => $this->fecha_nacimiento,
            'fecha_inicio_actividades' => $this->fecha_inicio_actividades,
            'fecha_fin_actividades' => $this->fecha_fin_actividades,
        ]);

        session()->flash('message', $this->empleado_id ? 'Empleado actualizado correctamente.' : 'Empleado creado correctamente.');
        $this->tab_activo = 'listado';
        $this->resetCampos();
        $this->dispatch('empleadoGuardado');
    }

    public function editar($id)
    {
        $this->tab_activo = 'nuevo';
        $empleado = $this->servicio->obtener($id);
        $this->empleado_id = $empleado->id_empleado;
        $this->id_rol_laboral = $empleado->id_rol_laboral;
        $this->dni = $empleado->dni;
        $this->apellido = $empleado->apellido;
        $this->nombre = $empleado->nombre;
        $this->fecha_nacimiento = $empleado->fecha_nacimiento;
        $this->fecha_inicio_actividades = $empleado->fecha_inicio_actividades;
        $this->fecha_fin_actividades = $empleado->fecha_fin_actividades;
    }

    public function eliminar($id)
    {
        $this->servicio->eliminar($id);
        session()->flash('message', 'Empleado eliminado correctamente.');
        $this->resetPage();
    }

    public function resetCampos()
    {
        $this->reset(['empleado_id', 'id_rol_laboral', 'dni', 'apellido', 'nombre', 'fecha_nacimiento', 'fecha_inicio_actividades', 'fecha_fin_actividades']);
    }
}
