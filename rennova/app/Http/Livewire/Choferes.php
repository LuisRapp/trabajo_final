<?php

namespace App\Http\Livewire;

use App\Services\ChoferService;
use Livewire\Component;
use Livewire\WithPagination;

class Choferes extends Component
{
    use WithPagination;

    public $clientes = [];

    public $tab_activo = 'listado';

    public $chofer_id;

    public $id_cliente;

    public $apellido;

    public $nombre;

    public $dni;

    public $telefono;

    public $direccion;

    public $estado = true;

    public $busqueda = '';

    protected ChoferService $servicio;

    public function boot(): void
    {
        $this->servicio = app(ChoferService::class);
    }

    protected function rules()
    {
        return $this->servicio->reglas($this->chofer_id);
    }

    public function mount()
    {
        $this->clientes = $this->servicio->listarClientes();
    }

    public function updatedBusqueda()
    {
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.choferes', [
            'choferes' => $this->servicio->listar($this->busqueda),
        ]);
    }

    public function guardar()
    {
        $this->validate();

        $this->servicio->crearOActualizar($this->chofer_id, [
            'id_cliente' => $this->id_cliente,
            'apellido' => $this->apellido,
            'nombre' => $this->nombre,
            'dni' => $this->dni,
            'telefono' => $this->telefono,
            'direccion' => $this->direccion,
            'estado' => $this->estado,
        ]);

        session()->flash('message', $this->chofer_id ? 'Chofer actualizado correctamente.' : 'Chofer creado correctamente.');
        $this->tab_activo = 'listado';
        $this->resetCampos();
        $this->dispatch('choferGuardado');
    }

    public function editar($id)
    {
        $this->tab_activo = 'nuevo';
        $chofer = $this->servicio->obtener($id);
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
        $this->servicio->eliminar($id);
        session()->flash('message', 'Chofer eliminado correctamente.');
        $this->resetCampos();
    }

    public function resetCampos()
    {
        $this->reset([
            'chofer_id', 'id_cliente', 'apellido', 'nombre', 'dni', 'telefono', 'direccion', 'estado',
        ]);
        $this->estado = true;
    }
}
