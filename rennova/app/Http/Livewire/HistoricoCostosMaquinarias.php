<?php

namespace App\Http\Livewire;

use App\Services\HistoricoCostosMaquinariaService;
use Livewire\Component;
use Livewire\WithPagination;

class HistoricoCostosMaquinarias extends Component
{
    use WithPagination;

    public $historico_id;

    public $id_maquinaria;

    public $costo_por_tonelada;

    public $fecha_inicio_vigencia;

    public $fecha_fin_vigencia;

    public $maquinarias;

    public $busqueda = '';

    public $tab_activo = 'listado';

    protected HistoricoCostosMaquinariaService $servicio;

    public function boot(): void
    {
        $this->servicio = app(HistoricoCostosMaquinariaService::class);
    }

    protected function rules(): array
    {
        return $this->servicio->reglas();
    }

    protected $messages = [
        'id_maquinaria.required' => 'Debe seleccionar una maquinaria.',
        'costo_por_tonelada.required' => 'El costo por tonelada es obligatorio.',
        'costo_por_tonelada.min' => 'El costo debe ser mayor o igual a 0.',
        'fecha_inicio_vigencia.required' => 'La fecha de inicio es obligatoria.',
        'fecha_fin_vigencia.after' => 'La fecha de fin debe ser posterior a la fecha de inicio.',
    ];

    public function mount()
    {
        $this->maquinarias = $this->servicio->listarMaquinarias();
    }

    public function render()
    {
        return view('livewire.historico-costos-maquinarias', [
            'historicos' => $this->servicio->listar($this->busqueda),
        ]);
    }

    public function updatedBusqueda()
    {
        $this->resetPage();
    }

    public function guardar()
    {
        $this->validate();

        $this->servicio->crearOActualizar($this->historico_id, [
            'id_maquinaria' => $this->id_maquinaria,
            'costo_por_tonelada' => $this->costo_por_tonelada,
            'fecha_inicio_vigencia' => $this->fecha_inicio_vigencia,
            'fecha_fin_vigencia' => $this->fecha_fin_vigencia,
        ]);

        session()->flash('message', $this->historico_id ? 'Histórico actualizado correctamente.' : 'Histórico creado correctamente.');
        $this->tab_activo = 'listado';
        $this->resetCampos();
        $this->dispatch('historicoGuardado');
    }

    public function editar($id)
    {
        $historico = $this->servicio->obtener($id);
        $this->tab_activo = 'nuevo';
        $this->historico_id = $historico->id_costo;
        $this->id_maquinaria = $historico->id_maquinaria;
        $this->costo_por_tonelada = $historico->costo_por_tonelada;
        $this->fecha_inicio_vigencia = $historico->fecha_inicio_vigencia;
        $this->fecha_fin_vigencia = $historico->fecha_fin_vigencia;
    }

    public function eliminar($id)
    {
        $this->servicio->eliminar($id);
        session()->flash('message', 'Histórico eliminado correctamente.');
    }

    public function resetCampos()
    {
        $this->reset(['historico_id', 'id_maquinaria', 'costo_por_tonelada', 'fecha_inicio_vigencia', 'fecha_fin_vigencia']);
    }
}
