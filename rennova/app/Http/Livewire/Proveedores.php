<?php

namespace App\Http\Livewire;

use App\Services\ProveedorService;
use Livewire\Component;
use Livewire\WithPagination;

class Proveedores extends Component
{
    use WithPagination;

    public $proveedor_id;

    public $razon_social;

    public $cuit;

    public $direccion;

    public $telefono;

    public $email;

    public $busqueda = '';

    public $tab_activo = 'listado';

    protected ProveedorService $servicio;

    public function boot(): void
    {
        $this->servicio = app(ProveedorService::class);
    }

    protected function rules(): array
    {
        return $this->servicio->reglas($this->proveedor_id);
    }

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
        return view('livewire.proveedores', [
            'proveedores' => $this->servicio->listar($this->busqueda),
        ]);
    }

    public function updatedBusqueda()
    {
        $this->resetPage();
    }

    public function guardar()
    {
        $this->validate();

        $this->servicio->crearOActualizar($this->proveedor_id, [
            'razon_social' => $this->razon_social,
            'cuit' => $this->cuit,
            'direccion' => $this->direccion,
            'telefono' => $this->telefono,
            'email' => $this->email,
        ]);

        session()->flash('message', $this->proveedor_id ? 'Proveedor actualizado correctamente.' : 'Proveedor creado correctamente.');
        $this->tab_activo = 'listado';
        $this->resetCampos();
        $this->dispatch('proveedorGuardado');
    }

    public function editar($id)
    {
        $this->tab_activo = 'nuevo';
        $proveedor = $this->servicio->obtener($id);
        $this->proveedor_id = $proveedor->id_proveedor;
        $this->razon_social = $proveedor->razon_social;
        $this->cuit = $proveedor->cuit;
        $this->direccion = $proveedor->direccion;
        $this->telefono = $proveedor->telefono;
        $this->email = $proveedor->email;
    }

    public function eliminar($id)
    {
        $this->servicio->eliminar($id);
        session()->flash('message', 'Proveedor eliminado correctamente.');
    }

    public function resetCampos()
    {
        $this->reset(['proveedor_id', 'razon_social', 'cuit', 'direccion', 'telefono', 'email']);
    }
}
