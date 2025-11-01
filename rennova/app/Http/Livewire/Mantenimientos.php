<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Mantenimiento;
use App\Models\Maquinaria;
use App\Models\TipoMantenimiento;

class Mantenimientos extends Component
{
    public $mantenimientos, $mantenimiento_id, $id_maquinaria, $id_tipo_mantenimiento, $fecha_inicio, $fecha_fin, $costo_total, $estado, $busqueda = '';
    public $maquinarias, $tipos;

    protected $rules = [
        'id_maquinaria' => 'required|exists:maquinarias,id_maquinaria',
        'id_tipo_mantenimiento' => 'required|exists:tipo_mantenimientos,id_tipo_mantenimiento',
        'fecha_inicio' => 'required|date',
        'fecha_fin' => 'nullable|date|after:fecha_inicio',
        'costo_total' => 'required|numeric|min:0',
        'estado' => 'required|in:programado,en curso,completado',
    ];

    protected $messages = [
        'id_maquinaria.required' => 'Debe seleccionar una maquinaria.',
        'id_tipo_mantenimiento.required' => 'Debe seleccionar un tipo de mantenimiento.',
        'fecha_inicio.required' => 'La fecha de inicio es obligatoria.',
        'fecha_fin.after' => 'La fecha de fin debe ser posterior a la fecha de inicio.',
        'costo_total.required' => 'El costo total es obligatorio.',
        'costo_total.min' => 'El costo debe ser mayor o igual a 0.',
        'estado.required' => 'El estado es obligatorio.',
    ];

    public function mount()
    {
        $this->maquinarias = Maquinaria::all();
        $this->tipos = TipoMantenimiento::all();
    }

    public function render()
    {
        $this->cargarMantenimientos();
        return view('livewire.mantenimientos');
    }

    public function cargarMantenimientos()
    {
        $query = Mantenimiento::with(['maquinaria', 'tipoMantenimiento']);

        if ($this->busqueda) {
            $busq = $this->busqueda;
            $query->where(function($q) use ($busq) {
                $q->where('estado', 'ILIKE', '%' . $busq . '%')
                  ->orWhereRaw("CAST(costo_total AS TEXT) ILIKE ?", ['%' . $busq . '%'])
                  ->orWhereHas('maquinaria', function($qm) use ($busq) {
                      $qm->where('modelo', 'ILIKE', '%' . $busq . '%');
                  })
                  ->orWhereHas('tipoMantenimiento', function($qt) use ($busq) {
                      $qt->where('nombre', 'ILIKE', '%' . $busq . '%');
                  });
            });
        }

        $this->mantenimientos = $query->orderBy('id_mantenimiento', 'desc')->get();
    }

    public function updatedBusqueda()
    {
        $this->cargarMantenimientos();
    }

    public function guardar()
    {
        $this->validate();

        Mantenimiento::updateOrCreate(
            ['id_mantenimiento' => $this->mantenimiento_id],
            [
                'id_maquinaria' => $this->id_maquinaria,
                'id_tipo_mantenimiento' => $this->id_tipo_mantenimiento,
                'fecha_inicio' => $this->fecha_inicio,
                'fecha_fin' => $this->fecha_fin,
                'costo_total' => $this->costo_total,
                'estado' => $this->estado,
            ]
        );

        session()->flash('message', $this->mantenimiento_id ? 'Mantenimiento actualizado correctamente.' : 'Mantenimiento creado correctamente.');
        $this->resetCampos();
        $this->dispatch('mantenimientoGuardado');
    }

    public function editar($id)
    {
        $mantenimiento = Mantenimiento::findOrFail($id);
        $this->mantenimiento_id = $mantenimiento->id_mantenimiento;
        $this->id_maquinaria = $mantenimiento->id_maquinaria;
        $this->id_tipo_mantenimiento = $mantenimiento->id_tipo_mantenimiento;
        $this->fecha_inicio = $mantenimiento->fecha_inicio;
        $this->fecha_fin = $mantenimiento->fecha_fin;
        $this->costo_total = $mantenimiento->costo_total;
        $this->estado = $mantenimiento->estado;
    }

    public function eliminar($id)
    {
        Mantenimiento::findOrFail($id)->delete();
        session()->flash('message', 'Mantenimiento eliminado correctamente.');
    }

    public function resetCampos()
    {
        $this->reset(['mantenimiento_id', 'id_maquinaria', 'id_tipo_mantenimiento', 'fecha_inicio', 'fecha_fin', 'costo_total', 'estado']);
    }
}
