<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Cliente;

class Clientes extends Component
{
    public $clientes, $cliente_id, $razon_social, $cuit, $direccion, $contacto, $busqueda = '';
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
           $this->cargarClientes();
        return view('livewire.clientes');
    }

        public function cargarClientes()
        {
            $query = Cliente::query();

            if ($this->busqueda) {
                $query->where(function($q) {
                    $q->where('razon_social', 'ILIKE', '%' . $this->busqueda . '%')
                      ->orWhere('cuit', 'ILIKE', '%' . $this->busqueda . '%')
                      ->orWhere('contacto', 'ILIKE', '%' . $this->busqueda . '%');
                });
            }

            $this->clientes = $query->orderBy('id_cliente', 'desc')->get();
        }

        public function updatedBusqueda()
        {
            $this->cargarClientes();
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
