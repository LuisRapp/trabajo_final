<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Proveedor;

class Proveedores extends Component
{
    public $proveedores, $proveedor_id, $razon_social, $cuit, $direccion, $telefono, $email, $busqueda = '';

    protected $rules = [
        'razon_social' => 'required|min:3',
        'cuit' => 'required|digits:11|unique:proveedors,cuit',
        'direccion' => 'required',
        'telefono' => 'nullable|string',
        'email' => 'nullable|email',
    ];

    protected $messages = [
        'razon_social.required' => 'La razón social es obligatoria.',
        'razon_social.min' => 'La razón social debe tener al menos 3 caracteres.',
        'cuit.required' => 'El CUIT es obligatorio.',
        'cuit.digits' => 'El CUIT debe tener 11 dígitos.',
        'cuit.unique' => 'Este CUIT ya está registrado.',
        'direccion.required' => 'La dirección es obligatoria.',
        'email.email' => 'El email debe ser válido.',
    ];

    public function render()
    {
        $this->cargarProveedores();
        return view('livewire.proveedores');
    }

    public function cargarProveedores()
    {
        $query = Proveedor::query();

        if ($this->busqueda) {
            $query->where(function($q) {
                $q->where('razon_social', 'ILIKE', '%' . $this->busqueda . '%')
                  ->orWhere('cuit', 'ILIKE', '%' . $this->busqueda . '%')
                  ->orWhere('email', 'ILIKE', '%' . $this->busqueda . '%');
            });
        }

        $this->proveedores = $query->orderBy('id_proveedor', 'desc')->get();
    }

    public function updatedBusqueda()
    {
        $this->cargarProveedores();
    }

    public function guardar()
    {
        $this->validate();

        Proveedor::updateOrCreate(
            ['id_proveedor' => $this->proveedor_id],
            [
                'razon_social' => $this->razon_social,
                'cuit' => $this->cuit,
                'direccion' => $this->direccion,
                'telefono' => $this->telefono,
                'email' => $this->email,
            ]
        );

        session()->flash('message', $this->proveedor_id ? 'Proveedor actualizado correctamente.' : 'Proveedor creado correctamente.');
        $this->resetCampos();
        $this->dispatch('proveedorGuardado');
    }

    public function editar($id)
    {
        $proveedor = Proveedor::findOrFail($id);
        $this->proveedor_id = $proveedor->id_proveedor;
        $this->razon_social = $proveedor->razon_social;
        $this->cuit = $proveedor->cuit;
        $this->direccion = $proveedor->direccion;
        $this->telefono = $proveedor->telefono;
        $this->email = $proveedor->email;
    }

    public function eliminar($id)
    {
        Proveedor::findOrFail($id)->delete();
        session()->flash('message', 'Proveedor eliminado correctamente.');
    }

    public function resetCampos()
    {
        $this->reset(['proveedor_id', 'razon_social', 'cuit', 'direccion', 'telefono', 'email']);
    }
}
