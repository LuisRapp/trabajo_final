<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\KitPreventivo;
use App\Models\Insumo;
use Illuminate\Support\Facades\DB;

class ConfiguracionInsumosKit extends Component
{
    public $kitId;
    public $kit;
    public $insumo_id;
    public $cantidad_necesaria;
    public $insumosKit = [];

    protected $rules = [
        'insumo_id' => 'required|exists:insumos,id_insumo',
        'cantidad_necesaria' => 'required|numeric|min:0.01',
    ];

    protected $messages = [
        'insumo_id.required' => 'Debe seleccionar un insumo.',
        'insumo_id.exists' => 'El insumo seleccionado no es válido.',
        'cantidad_necesaria.required' => 'La cantidad es obligatoria.',
        'cantidad_necesaria.numeric' => 'La cantidad debe ser un número.',
        'cantidad_necesaria.min' => 'La cantidad debe ser mayor a 0.',
    ];

    public function mount($kitId)
    {
        $this->kitId = $kitId;
        $this->kit = KitPreventivo::with('tipoMaquinaria')->findOrFail($kitId);
        $this->cargarInsumosKit();
    }

    public function render()
    {
        $insumos = Insumo::orderBy('nombre')->get();
        return view('livewire.configuracion-insumos-kit', compact('insumos'));
    }

    public function cargarInsumosKit()
    {
        $this->insumosKit = $this->kit->insumos()
            ->withPivot('cantidad_necesaria')
            ->get()
            ->map(function($insumo) {
                return [
                    'id_insumo' => $insumo->id_insumo,
                    'nombre' => $insumo->nombre,
                    'unidad_medida' => $insumo->unidad_medida,
                    'cantidad_necesaria' => $insumo->pivot->cantidad_necesaria,
                ];
            })->toArray();
    }

    public function agregarInsumo()
    {
        $this->validate();

        // Verificar si el insumo ya está en el kit
        $existe = $this->kit->insumos()->where('insumo_id', $this->insumo_id)->exists();
        
        if ($existe) {
            session()->flash('error', 'Este insumo ya está en el kit.');
            return;
        }

        // Agregar el insumo al kit
        $this->kit->insumos()->attach($this->insumo_id, [
            'cantidad_necesaria' => $this->cantidad_necesaria
        ]);

        session()->flash('message', 'Insumo agregado correctamente.');
        $this->reset(['insumo_id', 'cantidad_necesaria']);
        $this->cargarInsumosKit();
    }

    public function quitarInsumo($insumoId)
    {
        $this->kit->insumos()->detach($insumoId);
        session()->flash('message', 'Insumo quitado del kit.');
        $this->cargarInsumosKit();
    }
}
