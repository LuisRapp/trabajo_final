<?php

namespace App\Http\Livewire;

use App\Enums\TaskType;
use App\Http\Livewire\Traits\MensajesErrorUsuario;
use App\Services\AsignacionLoteService;
use App\Services\LoteService;
use Livewire\Component;
use Livewire\WithPagination;

class Lotes extends Component
{
    use MensajesErrorUsuario;
    use WithPagination;

    public $propietario;

    public $ubicacion;

    public $superficie;

    public $estado = 'activo';

    public $condicion_compra;

    public $especie;

    public $latitud;

    public $longitud;

    public $main_task_type;

    public $lote_id;

    public $busqueda = '';

    protected LoteService $servicio;

    public function boot(): void
    {
        $this->servicio = app(LoteService::class);
    }

    protected function rules(): array
    {
        return $this->servicio->reglas();
    }

    protected $messages = [
        'required' => 'Este campo es obligatorio.',
        'numeric' => 'Debe ingresar un número válido.',
        'min' => 'El valor es demasiado bajo.',
        'max' => 'El valor es demasiado alto.',
        'in' => 'Seleccione una opción válida.',
    ];

    public function mount()
    {
        $this->resetCampos();
    }

    public function updatedBusqueda()
    {
        $this->resetPage();
    }

    public function resetCampos()
    {
        $this->propietario = '';
        $this->ubicacion = '';
        $this->superficie = '';
        $this->estado = 'activo';
        $this->condicion_compra = '';
        $this->especie = '';
        $this->latitud = null;
        $this->longitud = null;
        $this->main_task_type = TaskType::TALA_RASA->value;
        $this->lote_id = null;
    }

    public function getTaskTypesProperty(): array
    {
        return TaskType::cases();
    }

    public function guardar()
    {
        $this->validate();

        try {
            $resultado = $this->servicio->crearOActualizar($this->lote_id, [
                'propietario' => $this->propietario,
                'ubicacion' => $this->ubicacion,
                'superficie' => $this->superficie,
                'estado' => $this->estado,
                'condicion_compra' => $this->condicion_compra,
                'especie' => $this->especie,
                'latitud' => $this->latitud,
                'longitud' => $this->longitud,
                'main_task_type' => $this->main_task_type,
            ]);

            $lote = $resultado['lote'];

            if ($resultado['tareas_activas'] !== null) {
                if ($this->lote_id) {
                    if ($resultado['tareas_activas'] === 0) {
                        session()->flash('message', 'El lote pasó a En explotación. Antes, planificá las tareas para generar propuestas de asignación reales (ej: 5 ha raleo + 5 ha tala rasa).');

                        return redirect()->route('lotes.tareas', ['loteId' => $lote->id_lote]);
                    }

                    session()->flash('message', 'Lote actualizado correctamente.');

                    return redirect()->route('lotes.recomendaciones', ['loteId' => $lote->id_lote]);
                }

                session()->flash('message', 'Lote creado en En explotación. Planificá tareas para generar propuestas de asignación.');

                return redirect()->route('lotes.tareas', ['loteId' => $lote->id_lote]);
            }

            session()->flash('message', $this->lote_id ? 'Lote actualizado correctamente.' : 'Lote creado correctamente.');
        } catch (\Throwable $e) {
            session()->flash('error', 'No se pudo guardar el lote. Verificá los datos e intentá nuevamente.');

            return;
        }
        $this->resetCampos();

        $this->dispatch('loteGuardado');
    }

    public function editar($id)
    {
        $lote = $this->servicio->obtener($id);
        $this->lote_id = $lote->id_lote;
        $this->propietario = $lote->propietario;
        $this->ubicacion = $lote->ubicacion;
        $this->superficie = $lote->superficie;
        $this->estado = $lote->estado;
        $this->condicion_compra = $lote->condicion_compra;
        $this->especie = $lote->especie;
        $this->latitud = $lote->latitud;
        $this->longitud = $lote->longitud;
        $this->main_task_type = $lote->main_task_type;
    }

    public function eliminar($id)
    {
        $this->servicio->eliminar($id);
        session()->flash('message', 'Lote eliminado correctamente.');
        $this->resetCampos();
    }

    public function finalizarLote($id)
    {
        try {
            $finalizado = app(AsignacionLoteService::class)->finalizar((int) $id, $this->datosSolicitud());

            if ($finalizado) {
                session()->flash('message', 'Lote finalizado correctamente. Los recursos han sido liberados.');
            } else {
                session()->flash('error', 'El lote ya está finalizado.');
            }
        } catch (\Throwable $e) {
            session()->flash('error', $this->mensajeErrorUsuario($e, 'finalizar el lote'));
        }
    }

    /**
     * Contexto del request para el registro de auditoría.
     *
     * @return array{user_id: ?int, ip_address: ?string, user_agent: ?string, url: ?string}
     */
    private function datosSolicitud(): array
    {
        return [
            'user_id' => auth()->id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'url' => request()->fullUrl(),
        ];
    }

    public function render()
    {
        return view('livewire.lotes', [
            'lotes' => $this->servicio->listar($this->busqueda),
        ]);
    }
}
