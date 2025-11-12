<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\KitPreventivo;
use App\Models\TipoMaquinaria;

class GestionKitPreventivo extends Component
{
    public $kits, $kit_id, $nombre_kit, $id_tipo_maquinaria, $busqueda = '';

    protected $rules = [
        'nombre_kit' => 'required|min:5',
        'id_tipo_maquinaria' => 'required|exists:tipo_maquinarias,id_tipo_maquinaria',
    ];

    protected $messages = [
        'nombre_kit.required' => 'El nombre del kit es obligatorio.',
        'nombre_kit.min' => 'El nombre debe tener al menos 5 caracteres.',
        'id_tipo_maquinaria.required' => 'Debe seleccionar un tipo de maquinaria.',
        'id_tipo_maquinaria.exists' => 'El tipo de maquinaria seleccionado no es válido.',
    ];

    public function render()
    {
        $this->cargarKits();
        $tiposMaquinaria = TipoMaquinaria::orderBy('nombre')->get();
        return view('livewire.gestion-kit-preventivo', compact('tiposMaquinaria'));
    }

    public function cargarKits()
    {
        $query = KitPreventivo::with('tipoMaquinaria');

        if ($this->busqueda) {
            $busq = $this->busqueda;
            $query->where('nombre_kit', 'ILIKE', '%' . $busq . '%')
                  ->orWhereHas('tipoMaquinaria', function($q) use ($busq) {
                      $q->where('nombre', 'ILIKE', '%' . $busq . '%');
                  });
        }

        $this->kits = $query->orderBy('id_kit_preventivo', 'desc')->get();
    }

    public function updatedBusqueda()
    {
        $this->cargarKits();
    }

    public function guardar()
    {
        $this->validate();

        KitPreventivo::updateOrCreate(
            ['id_kit_preventivo' => $this->kit_id],
            [
                'nombre_kit' => $this->nombre_kit,
                'id_tipo_maquinaria' => $this->id_tipo_maquinaria
            ]
        );

        session()->flash('message', $this->kit_id ? 'Kit actualizado correctamente.' : 'Kit creado correctamente.');
        $this->resetCampos();
        $this->dispatch('kitGuardado');
    }

    public function editar($id)
    {
        $kit = KitPreventivo::findOrFail($id);
        $this->kit_id = $kit->id_kit_preventivo;
        $this->nombre_kit = $kit->nombre_kit;
        $this->id_tipo_maquinaria = $kit->id_tipo_maquinaria;
    }

    public function eliminar($id)
    {
        $kit = KitPreventivo::findOrFail($id);
        $kit->delete(); // Soft delete
        session()->flash('message', 'Kit eliminado correctamente.');
    }

    public function resetCampos()
    {
        $this->reset(['kit_id', 'nombre_kit', 'id_tipo_maquinaria']);
    }
}
