<?php

namespace App\Http\Livewire;

use App\Services\InsumoService;
use Livewire\Component;
use Livewire\WithPagination;

class Insumos extends Component
{
    use WithPagination;

    public $insumo_id;

    public $nombre;

    public $descripcion;

    public $id_unidad_medida;

    public $id_proveedor;

    public $busqueda = '';

    public $unidades;

    public $proveedores;

    public $tab_activo = 'listado';

    protected InsumoService $servicio;

    public function boot(): void
    {
        $this->servicio = app(InsumoService::class);
    }

    protected function rules(): array
    {
        return $this->servicio->reglas();
    }

    protected $messages = [
        'nombre.required' => 'El nombre es obligatorio.',
        'nombre.min' => 'El nombre debe tener al menos 3 caracteres.',
        'id_unidad_medida.required' => 'Debe seleccionar una unidad de medida.',
        'id_proveedor.required' => 'Debe seleccionar un proveedor.',
    ];

    public function mount()
    {
        $this->unidades = $this->servicio->listarUnidades();
        $this->proveedores = $this->servicio->listarProveedores();
    }

    public function render()
    {
        return view('livewire.insumos', [
            'insumos' => $this->servicio->listar($this->busqueda),
        ]);
    }

    public function updatedBusqueda()
    {
        $this->resetPage();
    }

    public function guardar()
    {
        $this->validate();

        $this->servicio->crearOActualizar($this->insumo_id, [
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'id_unidad_medida' => $this->id_unidad_medida,
            'id_proveedor' => $this->id_proveedor,
        ]);

        session()->flash('message', $this->insumo_id ? 'Insumo actualizado correctamente.' : 'Insumo creado correctamente.');
        $this->tab_activo = 'listado';
        $this->resetCampos();
        $this->dispatch('insumoGuardado');
    }

    public function editar($id)
    {
        $this->tab_activo = 'nuevo';
        $insumo = $this->servicio->obtener($id);
        $this->insumo_id = $insumo->id_insumo;
        $this->nombre = $insumo->nombre;
        $this->descripcion = $insumo->descripcion;
        $this->id_unidad_medida = $insumo->id_unidad_medida;
        $this->id_proveedor = $insumo->id_proveedor;
    }

    public function eliminar($id)
    {
        $this->servicio->eliminar($id);
        session()->flash('message', 'Insumo eliminado correctamente.');
    }

    public function resetCampos()
    {
        $this->reset(['insumo_id', 'nombre', 'descripcion', 'id_unidad_medida', 'id_proveedor']);
    }
}
