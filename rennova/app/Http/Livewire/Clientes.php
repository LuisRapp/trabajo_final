<?php

namespace App\Http\Livewire;

use App\Services\ClienteService;
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

    protected ClienteService $servicio;

    public function boot(): void
    {
        $this->servicio = app(ClienteService::class);
    }

    protected function rules(): array
    {
        return $this->servicio->reglas($this->cliente_id);
    }

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
            'clientes' => $this->servicio->listar($this->busqueda),
        ]);
    }

    public function updatedBusqueda()
    {
        $this->resetPage();
    }

    public function guardar()
    {
        $this->validate();

        $this->servicio->crearOActualizar($this->cliente_id, [
            'razon_social' => $this->razon_social,
            'cuit' => $this->cuit,
            'direccion' => $this->direccion,
            'contacto' => $this->contacto,
        ]);

        session()->flash('message', $this->cliente_id ? 'Cliente actualizado correctamente.' : 'Cliente creado correctamente.');
        $this->tab_activo = 'listado';
        $this->resetCampos();
        $this->dispatch('clienteGuardado');
    }

    public function editar($id)
    {
        $this->tab_activo = 'nuevo';
        $cliente = $this->servicio->obtener($id);
        $this->cliente_id = $cliente->id_cliente;
        $this->razon_social = $cliente->razon_social;
        $this->cuit = $cliente->cuit;
        $this->direccion = $cliente->direccion;
        $this->contacto = $cliente->contacto;
    }

    public function eliminar($id)
    {
        $this->servicio->eliminar($id);
        session()->flash('message', 'Cliente eliminado correctamente.');
    }

    public function resetCampos()
    {
        $this->reset(['cliente_id', 'razon_social', 'cuit', 'direccion', 'contacto']);
    }
}
