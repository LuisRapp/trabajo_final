<?php

namespace App\Http\Livewire;

use App\Models\HistoricoCostosMaquinaria;
use App\Models\Maquinaria;
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

    protected $rules = [
        'id_maquinaria' => 'required|exists:maquinarias,id_maquinaria',
        'costo_por_tonelada' => 'required|numeric|min:0',
        'fecha_inicio_vigencia' => 'required|date',
        'fecha_fin_vigencia' => 'nullable|date|after:fecha_inicio_vigencia',
    ];

    protected $messages = [
        'id_maquinaria.required' => 'Debe seleccionar una maquinaria.',
        'costo_por_tonelada.required' => 'El costo por tonelada es obligatorio.',
        'costo_por_tonelada.min' => 'El costo debe ser mayor o igual a 0.',
        'fecha_inicio_vigencia.required' => 'La fecha de inicio es obligatoria.',
        'fecha_fin_vigencia.after' => 'La fecha de fin debe ser posterior a la fecha de inicio.',
    ];

    public function mount()
    {
        $this->maquinarias = Maquinaria::all();
    }

    public function render()
    {
        return view('livewire.historico-costos-maquinarias', [
            'historicos' => $this->cargarHistoricos(),
        ]);
    }

    public function cargarHistoricos()
    {
        $query = HistoricoCostosMaquinaria::with('maquinaria');

        if ($this->busqueda) {
            $busq = $this->busqueda;
            $query->where(function ($q) use ($busq) {
                $q->whereRaw('CAST(costo_por_tonelada AS TEXT) ILIKE ?', ["%{$busq}%"])
                    ->orWhereDate('fecha_inicio_vigencia', $busq)
                    ->orWhereDate('fecha_fin_vigencia', $busq)
                    ->orWhereHas('maquinaria', function ($qr) use ($busq) {
                        $qr->where('modelo', 'ILIKE', "%{$busq}%");
                    });
            });
        }

        return $query->orderBy('id_costo', 'desc')->paginate(15);
    }

    public function updatedBusqueda()
    {
        $this->resetPage();
    }

    public function guardar()
    {
        $this->validate();

        HistoricoCostosMaquinaria::updateOrCreate(
            ['id_costo' => $this->historico_id],
            [
                'id_maquinaria' => $this->id_maquinaria,
                'costo_por_tonelada' => $this->costo_por_tonelada,
                'fecha_inicio_vigencia' => $this->fecha_inicio_vigencia,
                'fecha_fin_vigencia' => $this->fecha_fin_vigencia,
            ]
        );

        session()->flash('message', $this->historico_id ? 'Histórico actualizado correctamente.' : 'Histórico creado correctamente.');
        $this->resetCampos();
        $this->dispatch('historicoGuardado');
    }

    public function editar($id)
    {
        $historico = HistoricoCostosMaquinaria::findOrFail($id);
        $this->historico_id = $historico->id_costo;
        $this->id_maquinaria = $historico->id_maquinaria;
        $this->costo_por_tonelada = $historico->costo_por_tonelada;
        $this->fecha_inicio_vigencia = $historico->fecha_inicio_vigencia;
        $this->fecha_fin_vigencia = $historico->fecha_fin_vigencia;
    }

    public function eliminar($id)
    {
        HistoricoCostosMaquinaria::findOrFail($id)->delete();
        session()->flash('message', 'Histórico eliminado correctamente.');
    }

    public function resetCampos()
    {
        $this->reset(['historico_id', 'id_maquinaria', 'costo_por_tonelada', 'fecha_inicio_vigencia', 'fecha_fin_vigencia']);
    }
}
