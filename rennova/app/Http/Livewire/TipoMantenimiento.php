<?php

namespace App\Http\Livewire;

use App\Services\TipoMantenimientoService;
use Livewire\Component;
use Livewire\WithPagination;

class TipoMantenimiento extends Component
{
    use WithPagination;

    public $tipo_id;

    public $nombre;

    public $comportamiento = 'otro';

    public $busqueda = '';

    public $tab_activo = 'listado';

    protected TipoMantenimientoService $servicio;

    public function boot(): void
    {
        $this->servicio = app(TipoMantenimientoService::class);
    }

    protected function rules(): array
    {
        return $this->servicio->reglas($this->tipo_id);
    }

    protected $messages = [
        'nombre.required' => 'El nombre es obligatorio.',
        'nombre.min' => 'El nombre debe tener al menos 3 caracteres.',
        'nombre.unique' => 'Este nombre ya existe.',
        'comportamiento.required' => 'El comportamiento es obligatorio.',
        'comportamiento.in' => 'El comportamiento seleccionado no es válido.',
    ];

    public function render()
    {
        return view('livewire.tipo-mantenimiento', [
            'tipos' => $this->servicio->listar($this->busqueda),
        ]);
    }

    public function updatedBusqueda()
    {
        $this->resetPage();
    }

    public function guardar()
    {
        $this->authorize($this->tipo_id ? 'editar-tipos-mantenimiento' : 'crear-tipos-mantenimiento');
        $this->validate();

        $this->servicio->crearOActualizar($this->tipo_id, [
            'nombre' => $this->nombre,
            'comportamiento' => $this->comportamiento,
        ]);

        session()->flash('message', $this->tipo_id ? 'Tipo actualizado correctamente.' : 'Tipo creado correctamente.');
        $this->tab_activo = 'listado';
        $this->resetCampos();
        $this->dispatch('tipoGuardado');
    }

    public function editar($id)
    {
        $this->authorize('editar-tipos-mantenimiento');
        $this->tab_activo = 'nuevo';
        $tipo = $this->servicio->obtener($id);
        $this->tipo_id = $tipo->id_tipo_mantenimiento;
        $this->nombre = $tipo->nombre;
        $this->comportamiento = $tipo->comportamiento?->value ?? 'otro';
    }

    public function eliminar($id)
    {
        $this->authorize('eliminar-tipos-mantenimiento');
        $this->servicio->eliminar($id);
        session()->flash('message', 'Tipo de mantenimiento dado de baja.');
    }

    public function resetCampos()
    {
        $this->reset(['tipo_id', 'nombre']);
        $this->comportamiento = 'otro';
    }
}
