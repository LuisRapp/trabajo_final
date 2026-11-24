<?php

namespace App\Http\Livewire;

use App\Http\Livewire\Traits\MensajesErrorUsuario;
use App\Services\KitMantenimientoPreventivoService;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class ConfiguracionKits extends Component
{
    use MensajesErrorUsuario;

    public $maquinaria_seleccionada = '';

    public $items_kit = [];

    public $kits_registrados = [];

    public $tab_activo = 'listado';

    // Item del kit que se esta agregando o editando
    public $item_id = null;

    public $insumo_id = '';

    public $cantidad_requerida = '';

    public $es_obligatorio = true;

    // Distingue editar un kit existente de configurar uno nuevo (solo cambia el titulo)
    public $editando_kit = false;

    protected KitMantenimientoPreventivoService $servicio;

    protected $rules = [
        'insumo_id' => 'required|exists:insumos,id_insumo',
        'cantidad_requerida' => 'required|numeric|min:0.01',
        'es_obligatorio' => 'boolean',
    ];

    protected $messages = [
        'insumo_id.required' => 'Debe seleccionar un insumo',
        'cantidad_requerida.required' => 'La cantidad es requerida',
        'cantidad_requerida.min' => 'La cantidad debe ser mayor a 0',
    ];

    public function boot(): void
    {
        $this->servicio = app(KitMantenimientoPreventivoService::class);
    }

    public function mount()
    {
        $this->cargarKitsRegistrados();
    }

    public function cargarKitsRegistrados(): void
    {
        $this->kits_registrados = $this->servicio->listarPorMaquinaria();
    }

    public function updatedMaquinariaSeleccionada(): void
    {
        $this->editando_kit = false;
        $this->cargarItemsKit();
        $this->resetearFormulario();
    }

    public function cargarItemsKit(): void
    {
        $this->items_kit = $this->servicio->itemsDeMaquinaria($this->maquinaria_seleccionada ?: null);
    }

    public function editarKit($idMaquinaria): void
    {
        $this->authorize('editar-kits-mantenimiento');

        $this->maquinaria_seleccionada = $idMaquinaria;
        $this->editando_kit = true;
        $this->tab_activo = 'nuevo';
        $this->cargarItemsKit();
        $this->resetearFormulario();
    }

    public function eliminarKit($idMaquinaria): void
    {
        $this->authorize('eliminar-kits-mantenimiento');

        try {
            $this->servicio->eliminarKit((int) $idMaquinaria);

            if ((string) $this->maquinaria_seleccionada === (string) $idMaquinaria) {
                $this->maquinaria_seleccionada = '';
                $this->items_kit = [];
                $this->editando_kit = false;
            }

            $this->cargarKitsRegistrados();
            session()->flash('message', 'Kit eliminado correctamente');
        } catch (\Exception $e) {
            Log::error('Error al eliminar kit: '.$e->getMessage());
            session()->flash('error', $this->mensajeErrorUsuario($e, 'eliminar el kit'));
        }
    }

    public function nuevoItem(): void
    {
        $this->resetearFormulario();
    }

    public function editarItem($itemId): void
    {
        $item = $this->servicio->obtenerItem((int) $itemId);

        $this->item_id = $item->id_kit;
        $this->insumo_id = $item->id_insumo;
        $this->cantidad_requerida = $item->cantidad_requerida;
        $this->es_obligatorio = $item->es_obligatorio;
    }

    public function cancelarEdicionItem(): void
    {
        $this->resetearFormulario();
    }

    public function resetearFormulario(): void
    {
        $this->item_id = null;
        $this->insumo_id = '';
        $this->cantidad_requerida = '';
        $this->es_obligatorio = true;
        $this->resetValidation();
    }

    public function guardar(): void
    {
        $this->authorize($this->item_id ? 'editar-kits-mantenimiento' : 'crear-kits-mantenimiento');
        $this->validate();

        try {
            $maquinaria = $this->servicio->obtenerMaquinaria($this->maquinaria_seleccionada ?: null);

            if (! $maquinaria) {
                session()->flash('error', 'Debe seleccionar una maquinaria válida antes de agregar insumos.');

                return;
            }

            if ($this->servicio->existeInsumoEnKit((int) $maquinaria->id_maquinaria, (int) $this->insumo_id, $this->item_id)) {
                session()->flash('error', 'Este insumo ya está en el kit. Use editar para modificarlo.');

                return;
            }

            $datos = [
                'id_maquinaria' => $maquinaria->id_maquinaria,
                // Columna legada NOT NULL: se mantiene por compatibilidad
                'id_tipo_maquinaria' => $maquinaria->id_tipo_maquinaria,
                'id_insumo' => $this->insumo_id,
                'cantidad_requerida' => $this->cantidad_requerida,
                'es_obligatorio' => (bool) $this->es_obligatorio,
            ];

            if ($this->item_id) {
                $this->servicio->actualizar((int) $this->item_id, $datos);
                session()->flash('message', 'Insumo actualizado correctamente');
            } else {
                $this->servicio->agregar($datos);
                session()->flash('message', 'Insumo agregado al kit correctamente');
            }

            $this->cargarItemsKit();
            $this->cargarKitsRegistrados();
            $this->resetearFormulario();
        } catch (\Exception $e) {
            Log::error('Error al guardar item del kit: '.$e->getMessage());
            session()->flash('error', $this->mensajeErrorUsuario($e, 'guardar la configuración'));
        }
    }

    public function eliminar($itemId): void
    {
        $this->authorize('eliminar-kits-mantenimiento');

        try {
            $this->servicio->eliminarItem((int) $itemId);
            session()->flash('message', 'Insumo quitado del kit');

            $this->cargarItemsKit();
            $this->cargarKitsRegistrados();
        } catch (\Exception $e) {
            Log::error('Error al eliminar item del kit: '.$e->getMessage());
            session()->flash('error', $this->mensajeErrorUsuario($e, 'eliminar el insumo del kit'));
        }
    }

    public function restaurar($itemId): void
    {
        $this->authorize('editar-kits-mantenimiento');

        try {
            if ($this->servicio->restaurarItem((int) $itemId)) {
                session()->flash('message', 'Item restaurado correctamente');
            }

            $this->cargarItemsKit();
            $this->cargarKitsRegistrados();
        } catch (\Exception $e) {
            Log::error('Error al restaurar item del kit: '.$e->getMessage());
            session()->flash('error', $this->mensajeErrorUsuario($e, 'restaurar el insumo'));
        }
    }

    public function getMaquinariasProperty()
    {
        return $this->servicio->maquinarias();
    }

    public function getInsumosProperty()
    {
        return $this->servicio->insumos();
    }

    public function getMaquinariaSeleccionadaProperty()
    {
        return $this->servicio->obtenerMaquinaria($this->maquinaria_seleccionada ?: null);
    }

    public function render()
    {
        $items = collect($this->items_kit);

        return view('livewire.configuracion-kits', [
            'maquinaria_kit' => $this->maquinariaSeleccionada,
            'maquinarias' => $this->maquinarias,
            'insumos' => $this->insumos,
            'items' => $items,
            'items_count' => $items->count(),
            'items_obligatorios' => $items->where('es_obligatorio', true)->count(),
            'items_con_stock' => $items->filter(function ($item): bool {
                $stock = optional($item->insumo)->stock;

                return is_numeric($stock) && $stock >= $item->cantidad_requerida;
            })->count(),
        ]);
    }
}
