<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Lote;

class Lotes extends Component
{
    public $lotes;
    public $propietario;
    public $ubicacion;
    public $superficie;
    public $estado = 'activo';
    public $condicion_compra;
    public $especie;
    public $latitud;
    public $longitud;
    public $lote_id;
    public $busqueda = '';

    protected $rules = [
        'propietario' => 'required|string|min:3|max:100',
        'ubicacion' => 'required|string|min:3|max:150',
        'especie' => 'required|string|min:2|max:100',
        'superficie' => 'required|numeric|min:0.1|max:10000',
        'condicion_compra' => 'required|in:propio,alquilado',
        'estado' => 'required|in:activo,inactivo',
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
        $this->cargarLotes();
    }

    public function cargarLotes()
    {
        $query = Lote::query();

        // Aplicar búsqueda si existe
        if (!empty($this->busqueda)) {
            $query->where(function($q) {
                $q->where('propietario', 'ILIKE', '%' . $this->busqueda . '%')
                  ->orWhere('ubicacion', 'ILIKE', '%' . $this->busqueda . '%')
                  ->orWhere('especie', 'ILIKE', '%' . $this->busqueda . '%');
            });
        }

        $this->lotes = $query->orderBy('id_lote', 'desc')->get();
    }

    // Actualizar listado cuando cambie la búsqueda
    public function updatedBusqueda()
    {
        $this->cargarLotes();
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
        $this->lote_id = null;
    }

    public function guardar()
    {
        $this->validate();
        if ($this->lote_id) {
            $lote = Lote::find($this->lote_id);
            $lote->update($this->only(['propietario','ubicacion','superficie','estado','condicion_compra','especie','latitud','longitud']));
            session()->flash('message', 'Lote actualizado correctamente.');
        } else {
            Lote::create($this->only(['propietario','ubicacion','superficie','estado','condicion_compra','especie','latitud','longitud']));
            session()->flash('message', 'Lote creado correctamente.');
        }
        $this->resetCampos();
        $this->cargarLotes();
        
        // Emitir evento para cambiar a la pestaña de listado
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
    }

    public function eliminar($id)
    {
        Lote::destroy($id);
        session()->flash('message', 'Lote eliminado correctamente.');
        $this->resetCampos();
        $this->cargarLotes();
    }

    public function render()
    {
        return view('livewire.lotes');
    }
}
