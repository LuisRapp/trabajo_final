<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Chofer;
use App\Models\Cliente;

class Choferes extends Component
{
    public $choferes;
    public $clientes = [];

    public $chofer_id;
    public $id_cliente;
    public $apellido;
    public $nombre;
    public $dni;
    public $telefono;
    public $direccion;
    public $estado = true; // tratar como booleano

    public $busqueda = '';

    protected function rules()
    {
        $id = $this->chofer_id ?? 'NULL';
        return [
            'id_cliente' => 'required|exists:clientes,id_cliente',
            'apellido'   => 'required|string|max:100',
            'nombre'     => 'required|string|max:100',
            'dni'        => 'required|string|max:20|unique:choferes,dni,' . $id . ',id_chofer',
            'telefono'   => 'nullable|string|max:30',
            'direccion'  => 'nullable|string|max:150',
            'estado'     => 'boolean',
        ];
    }

    public function mount()
    {
        $this->clientes = Cliente::orderBy('razon_social')->get();
        $this->choferes = [];
        $this->cargarChoferes();
    }

    public function cargarChoferes()
    {
        $query = Chofer::with('cliente');

        if ($this->busqueda) {
            $busq = $this->busqueda;
            $query->where(function($q) use ($busq) {
                $q->where('apellido', 'ILIKE', "%{$busq}%")
                  ->orWhere('nombre', 'ILIKE', "%{$busq}%")
                  ->orWhere('dni', 'ILIKE', "%{$busq}%")
                  ->orWhere('telefono', 'ILIKE', "%{$busq}%")
                  ->orWhere('direccion', 'ILIKE', "%{$busq}%")
                  ->orWhereHas('cliente', function($qr) use ($busq) {
                      $qr->where('razon_social', 'ILIKE', "%{$busq}%");
                  });
            });
        }

        $this->choferes = $query->orderByDesc('id_chofer')->get();
    }

    public function updatedBusqueda()
    {
        $this->cargarChoferes();
    }

    public function render()
    {
        return view('livewire.choferes');
    }

    public function guardar()
    {
        $this->validate();

        Chofer::updateOrCreate(
            ['id_chofer' => $this->chofer_id],
            [
                'id_cliente' => $this->id_cliente,
                'apellido'   => $this->apellido,
                'nombre'     => $this->nombre,
                'dni'        => $this->dni,
                'telefono'   => $this->telefono,
                'direccion'  => $this->direccion,
                'estado'     => (bool) $this->estado,
            ]
        );

        $this->cargarChoferes();
        session()->flash('message', $this->chofer_id ? 'Chofer actualizado correctamente.' : 'Chofer creado correctamente.');
        $this->resetCampos();
        $this->dispatch('choferGuardado');
    }

    public function editar($id)
    {
        $chofer = Chofer::findOrFail($id);
        $this->chofer_id = $chofer->id_chofer;
        $this->id_cliente = $chofer->id_cliente;
        $this->apellido = $chofer->apellido;
        $this->nombre = $chofer->nombre;
        $this->dni = $chofer->dni;
        $this->telefono = $chofer->telefono;
        $this->direccion = $chofer->direccion;
        $this->estado = (bool) $chofer->estado;
    }

    public function eliminar($id)
    {
        Chofer::findOrFail($id)->delete();
        $this->cargarChoferes();
        session()->flash('message', 'Chofer eliminado correctamente.');
        $this->resetCampos();
    }

    public function resetCampos()
    {
        $this->reset([
            'chofer_id', 'id_cliente', 'apellido', 'nombre', 'dni', 'telefono', 'direccion', 'estado'
        ]);
        $this->estado = true;
    }
}
