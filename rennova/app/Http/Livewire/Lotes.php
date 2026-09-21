<?php

namespace App\Http\Livewire;

use App\Enums\TaskType;
use App\Http\Livewire\Traits\MensajesErrorUsuario;
use App\Models\Lote;
use App\Models\LoteTarea;
use App\Services\AsignacionLoteService;
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

    protected $rules = [
        'propietario' => 'required|string|min:3|max:100',
        'ubicacion' => 'required|string|min:3|max:150',
        'especie' => 'required|string|min:2|max:100',
        'superficie' => 'required|numeric|min:0.1|max:10000',
        'condicion_compra' => 'required|in:propio,alquilado',
        'estado' => 'required|in:activo,en_proceso,inactivo,cerrado,baja',
        'main_task_type' => 'required|in:tala_rasa,raleo,poda,limpieza',
        'latitud' => 'nullable|numeric|between:-90,90',
        'longitud' => 'nullable|numeric|between:-180,180',
    ];

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

    public function cargarLotes()
    {
        $query = Lote::query();

        if (! empty($this->busqueda)) {
            $query->where(function ($q) {
                $q->where('propietario', 'ILIKE', '%'.$this->busqueda.'%')
                    ->orWhere('ubicacion', 'ILIKE', '%'.$this->busqueda.'%')
                    ->orWhere('especie', 'ILIKE', '%'.$this->busqueda.'%');
            });
        }

        return $query->orderBy('id_lote', 'desc')->paginate(15);
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

    // =========================================================================
    // CRUD — delegates to Eloquent (simple operations)
    // =========================================================================

    public function guardar()
    {
        $this->validate();

        try {
            if ($this->lote_id) {
                $lote = Lote::find($this->lote_id);
                $fromEstado = $lote->estado;
                $lote->update($this->only(['propietario', 'ubicacion', 'superficie', 'estado', 'condicion_compra', 'especie', 'latitud', 'longitud', 'main_task_type']));
                session()->flash('message', 'Lote actualizado correctamente.');

                if ($fromEstado !== 'en_proceso' && $lote->estado === 'en_proceso') {
                    $tareasActivas = LoteTarea::query()
                        ->where('id_lote', $lote->id_lote)
                        ->whereIn('estado', ['planificada', 'en_ejecucion'])
                        ->count();

                    if ($tareasActivas === 0) {
                        session()->flash('message', 'El lote pasó a En explotación. Antes, planificá las tareas para generar propuestas de asignación reales (ej: 5 ha raleo + 5 ha tala rasa).');

                        return redirect()->route('lotes.tareas', ['loteId' => $lote->id_lote]);
                    }

                    return redirect()->route('lotes.recomendaciones', ['loteId' => $lote->id_lote]);
                }
            } else {
                $lote = Lote::create($this->only(['propietario', 'ubicacion', 'superficie', 'estado', 'condicion_compra', 'especie', 'latitud', 'longitud', 'main_task_type']));

                if ($lote->estado === 'en_proceso') {
                    session()->flash('message', 'Lote creado en En explotación. Planificá tareas para generar propuestas de asignación.');

                    return redirect()->route('lotes.tareas', ['loteId' => $lote->id_lote]);
                }

                session()->flash('message', 'Lote creado correctamente.');
            }
        } catch (\Throwable $e) {
            session()->flash('error', 'No se pudo guardar el lote. Verificá los datos e intentá nuevamente.');

            return;
        }
        $this->resetCampos();

        $this->dispatch('loteGuardado');
    }

    public function editar($id)
    {
        $lote = Lote::findOrFail($id);
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
        Lote::destroy($id);
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
            'lotes' => $this->cargarLotes(),
        ]);
    }
}
