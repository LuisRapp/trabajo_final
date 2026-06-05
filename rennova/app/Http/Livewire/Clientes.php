<?php

namespace App\Http\Livewire;

use App\Models\Cliente;
use Livewire\Component;
use Livewire\WithPagination;

class Clientes extends Component
{
    use WithPagination;

    public $cliente_id;

    public $razon_social;

    public $cuit;

    public $direccion;

    public $contacto;

    public $busqueda = '';

    public $tab_activo = 'listado';

    protected $rules = [
        'razon_social' => 'required|min:3',
        'cuit' => 'required|digits:11|unique:clientes,cuit',
        'direccion' => 'required',
        'contacto' => 'nullable|string',
    ];

    protected $messages = [
        'razon_social.required' => 'La razón social es obligatoria.',
        'razon_social.min' => 'La razón social debe tener al menos 3 caracteres.',
        'cuit.required' => 'El CUIT es obligatorio.',
        'cuit.digits' => 'El CUIT debe tener 11 dígitos.',
        'cuit.unique' => 'Este CUIT ya está registrado.',
        'direccion.required' => 'La dirección es obligatoria.',
    ];

    public function render()
    {
        return view('livewire.clientes', [
            'clientes' => $this->cargarClientes(),
        ]);
    }

    public function cargarClientes()
    {
        $query = Cliente::query();

        if ($this->busqueda) {
            $query->where(function ($q) {
                $q->where('razon_social', 'ILIKE', '%'.$this->busqueda.'%')
                    ->orWhere('cuit', 'ILIKE', '%'.$this->busqueda.'%')
                    ->orWhere('contacto', 'ILIKE', '%'.$this->busqueda.'%');
            });
        }

        return $query->orderBy('id_cliente', 'desc')->paginate(15);
    }

    public function updatedBusqueda()
    {
        $this->resetPage();
    }

    public function guardar()
    {
        $this->validate();

        Cliente::updateOrCreate(
            ['id_cliente' => $this->cliente_id],
            [
                'razon_social' => $this->razon_social,
                'cuit' => $this->cuit,
                'direccion' => $this->direccion,
                'contacto' => $this->contacto,
            ]
        );

        session()->flash('message', $this->cliente_id ? 'Cliente actualizado correctamente.' : 'Cliente creado correctamente.');
        $this->tab_activo = 'listado';
        $this->resetCampos();
        $this->dispatch('clienteGuardado');
    }

    public function editar($id)
    {
        $this->tab_activo = 'nuevo';
        $cliente = Cliente::findOrFail($id);
        $this->cliente_id = $cliente->id_cliente;
        $this->razon_social = $cliente->razon_social;
        $this->cuit = $cliente->cuit;
        $this->direccion = $cliente->direccion;
        $this->contacto = $cliente->contacto;
    }

    public function eliminar($id)
    {
        Cliente::findOrFail($id)->delete();
        session()->flash('message', 'Cliente eliminado correctamente.');
    }

    public function resetCampos()
    {
        $this->reset(['cliente_id', 'razon_social', 'cuit', 'direccion', 'contacto']);
    }
}
