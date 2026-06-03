<?php

namespace App\Http\Livewire;

use App\Models\Empleado;
use App\Models\RolLaboral;
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

    public function rules()
    {
        return [
            'id_rol_laboral' => 'required|exists:roles_laborales,id_rol_laboral',
            'dni' => $this->empleado_id
                ? 'nullable|digits:8|unique:empleados,dni,'.$this->empleado_id.',id_empleado'
                : 'required|digits:8|unique:empleados,dni',
            'apellido' => 'required|min:2',
            'nombre' => 'required|min:2',
            'fecha_nacimiento' => 'required|date',
            'fecha_inicio_actividades' => 'required|date',
            'fecha_fin_actividades' => 'nullable|date|after:fecha_inicio_actividades',
        ];
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
        $this->roles = RolLaboral::all();
        $this->empleados = $this->obtenerEmpleados();
    }

    public function render()
    {
        return view('livewire.empleados', [
            'empleados' => $this->obtenerEmpleados()->paginate(10),
        ]);
    }

    public function obtenerEmpleados()
    {
        $query = Empleado::with('rolLaboral');

        if ($this->busqueda) {
            $busq = $this->busqueda;
            $query->where(function ($q) use ($busq) {
                $q->where('apellido', 'ILIKE', '%'.$busq.'%')
                    ->orWhere('nombre', 'ILIKE', '%'.$busq.'%')
                    ->orWhereRaw('CAST(dni AS TEXT) ILIKE ?', ['%'.$busq.'%'])
                    ->orWhereHas('rolLaboral', function ($qr) use ($busq) {
                        $qr->where('nombre', 'ILIKE', '%'.$busq.'%');
                    });
            });
        }

        return $query->orderBy('id_empleado', 'desc');
    }

    public function cargarEmpleados()
    {
        $this->empleados = $this->obtenerEmpleados();
    }

    public function updatedBusqueda()
    {
        $this->resetPage();
        $this->cargarEmpleados();
    }

    public function guardar()
    {
        $this->validate();

        Empleado::updateOrCreate(
            ['id_empleado' => $this->empleado_id],
            [
                'id_rol_laboral' => $this->id_rol_laboral,
                'dni' => $this->dni,
                'apellido' => $this->apellido,
                'nombre' => $this->nombre,
                'fecha_nacimiento' => $this->fecha_nacimiento,
                'fecha_inicio_actividades' => $this->fecha_inicio_actividades,
                'fecha_fin_actividades' => $this->fecha_fin_actividades,
            ]
        );

        session()->flash('message', $this->empleado_id ? 'Empleado actualizado correctamente.' : 'Empleado creado correctamente.');
        $this->resetCampos();
        $this->dispatch('empleadoGuardado');
    }

    public function editar($id)
    {
        $empleado = Empleado::findOrFail($id);
        $this->empleado_id = $empleado->id_empleado;
        $this->id_rol_laboral = $empleado->id_rol_laboral;
        $this->dni = $empleado->dni;
        $this->apellido = $empleado->apellido;
        $this->nombre = $empleado->nombre;
        $this->fecha_nacimiento = $empleado->fecha_nacimiento;
        $this->fecha_inicio_actividades = $empleado->fecha_inicio_actividades;
        $this->fecha_fin_actividades = $empleado->fecha_fin_actividades;
        $this->tab_activo = 'nuevo';
    }

    public function eliminar($id)
    {
        Empleado::findOrFail($id)->delete();
        session()->flash('message', 'Empleado eliminado correctamente.');
        $this->resetPage();
    }

    public function resetCampos()
    {
        $this->reset(['empleado_id', 'id_rol_laboral', 'dni', 'apellido', 'nombre', 'fecha_nacimiento', 'fecha_inicio_actividades', 'fecha_fin_actividades']);
    }
}
