<?php

namespace App\Http\Livewire;

use App\Http\Livewire\Traits\MensajesErrorUsuario;
use App\Models\TipoMantenimiento;
use App\Services\MantenimientoService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Mantenimientos extends Component
{
    use MensajesErrorUsuario;

    public $mantenimientos;

    public $mantenimiento_id;

    public $id_maquinaria;

    public $id_tipo_mantenimiento;

    public $fecha_inicio;

    public $fecha_programada;

    public $estado;

    public $busqueda = '';

    public $maquinarias;

    public $tipos;

    public $kitPreventivo = [];

    public $tab_activo = 'listado';

    protected function rules()
    {
        return [
            'id_maquinaria' => 'required|exists:maquinarias,id_maquinaria',
            'id_tipo_mantenimiento' => 'required|exists:tipo_mantenimientos,id_tipo_mantenimiento',
            'fecha_inicio' => 'required|date',
            'fecha_programada' => [
                'nullable',
                'date',
                'after_or_equal:fecha_inicio',
                function ($attribute, $value, $fail) {
                    if (! $value) {
                        return;
                    }

                    $servicio = app(MantenimientoService::class);

                    if ($this->mantenimiento_id) {
                        $mensaje = $servicio->validarFechaProgramadaEdicion($this->mantenimiento_id, $value);
                    } else {
                        $mensaje = $servicio->validarFechaProgramadaNueva($value);
                    }

                    if ($mensaje) {
                        $fail($mensaje);
                    }
                },
            ],
            'estado' => 'required|in:programado,en curso',
        ];
    }

    protected $messages = [
        'id_maquinaria.required' => 'Debe seleccionar una maquinaria.',
        'id_tipo_mantenimiento.required' => 'Debe seleccionar un tipo de mantenimiento.',
        'fecha_inicio.required' => 'La fecha de inicio es obligatoria.',
        'estado.required' => 'El estado es obligatorio.',
    ];

    public function mount()
    {
        $servicio = app(MantenimientoService::class);

        $this->maquinarias = $servicio->obtenerMaquinariasActivas();
        $this->tipos = $servicio->obtenerTiposMantenimiento();
        $this->fecha_inicio = date('Y-m-d');
        $this->estado = 'programado';
        $this->tab_activo = 'listado';
    }

    public function updatedIdMaquinaria()
    {
        $this->cargarKitPreventivo();
    }

    public function updatedIdTipoMantenimiento()
    {
        $this->cargarKitPreventivo();
    }

    public function cargarKitPreventivo()
    {
        $this->kitPreventivo = app(MantenimientoService::class)
            ->obtenerKitPreventivoParaMaquinaria((int) $this->id_maquinaria, (int) $this->id_tipo_mantenimiento);
    }

    /**
     * Delegado de la regla de dominio: si el tipo seleccionado es preventivo.
     * La vista lo usa para mostrar el bloque de kit preventivo.
     */
    public function esTipoPreventivo(TipoMantenimiento $tipo): bool
    {
        return app(MantenimientoService::class)->esTipoPreventivo($tipo);
    }

    public function render()
    {
        $this->cargarMantenimientos();

        return view('livewire.mantenimientos');
    }

    public function cargarMantenimientos()
    {
        $this->mantenimientos = app(MantenimientoService::class)->listarMantenimientos($this->busqueda);
    }

    public function updatedBusqueda()
    {
        $this->cargarMantenimientos();
    }

    public function guardar()
    {
        $this->validate();

        $servicio = app(MantenimientoService::class);
        $fechaInicio = $this->fecha_programada ?: $this->fecha_inicio;

        $resultado = $servicio->guardarMantenimiento(
            [
                'id_maquinaria' => $this->id_maquinaria,
                'id_tipo_mantenimiento' => $this->id_tipo_mantenimiento,
                'fecha_inicio' => $fechaInicio,
                'fecha_programada' => $this->fecha_programada,
                'estado' => $this->estado,
            ],
            $this->mantenimiento_id ?: null,
            Auth::id()
        );

        if (! $resultado['success']) {
            session()->flash('error', $resultado['message']);

            return;
        }

        $tipoNombre = $resultado['tipoNombre'];
        $maquinaNombre = $resultado['maquinaNombre'];

        session()->flash('message', "Orden de {$tipoNombre} creada correctamente para {$maquinaNombre}.");
        $this->resetCampos();
        $this->tab_activo = 'listado';
        $this->dispatch('mantenimientoGuardado');
    }

    public function editar($id)
    {
        $mantenimiento = app(MantenimientoService::class)->obtenerMantenimientoParaEditar($id);

        $this->mantenimiento_id = $mantenimiento->id_mantenimiento;
        $this->id_maquinaria = $mantenimiento->id_maquinaria;
        $this->id_tipo_mantenimiento = $mantenimiento->id_tipo_mantenimiento;
        $this->fecha_inicio = $mantenimiento->fecha_inicio;
        $this->fecha_programada = $mantenimiento->fecha_programada;
        $this->estado = $mantenimiento->estado;
        $this->tab_activo = 'nuevo';
    }

    public function eliminar($id)
    {
        app(MantenimientoService::class)->eliminarMantenimiento($id);
        session()->flash('message', 'Mantenimiento eliminado correctamente.');
    }

    public function resetCampos()
    {
        $this->reset(['mantenimiento_id', 'id_maquinaria', 'id_tipo_mantenimiento', 'fecha_programada']);
        $this->fecha_inicio = date('Y-m-d');
        $this->estado = 'programado';
        $this->kitPreventivo = [];
    }

    public function abrirModalCompletar($id)
    {
        $this->dispatch('abrirCompletarOrden', $id);
    }

    public function onOrdenCompletada(): void
    {
        $this->cargarMantenimientos();
    }

    public function confirmarMantenimiento($id)
    {
        $resultado = app(MantenimientoService::class)->confirmarMantenimiento($id, Auth::id());

        if ($resultado['success']) {
            session()->flash('message', $resultado['message']);
        } else {
            session()->flash('error', $resultado['message']);
        }

        $this->cargarMantenimientos();
    }

    public function reprogramarMantenimiento($id)
    {
        $resultado = app(MantenimientoService::class)->reprogramarMantenimiento($id);

        if ($resultado['success']) {
            session()->flash('message', $resultado['message']);
            $this->editar($id);
        } else {
            session()->flash('error', $resultado['message']);
        }
    }
}
