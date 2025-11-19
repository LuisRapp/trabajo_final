<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\KitMantenimientoPreventivo;
use App\Models\TipoMaquinaria;
use App\Models\Maquinaria;
use App\Models\Insumo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ConfiguracionKits extends Component
{
    // Selección actual (nuevo: por maquinaria específica)
    public $maquinaria_seleccionada = '';
    // Propiedad legada (compat) para no romper si la vista vieja aún la usa
    public $tipo_maquinaria_seleccionada = '';
    public $items_kit = [];
    public $kits_registrados = [];
    
    // Umbral de toneladas
    public $umbral_toneladas = 0;
    
    // Modal agregar/editar
    public $modal_item = false;
    public $item_id = null;
    public $insumo_id = '';
    public $cantidad_requerida = 0;
    public $es_obligatorio = true;
    
    // Control de cambios
    public $kit_modificado = false;
    
    // Edición de kit existente
    public $editando_kit = false;
    
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
    
    public function mount()
    {
        $this->cargarKitsRegistrados();
    }
    
    public function cargarKitsRegistrados()
    {
        // Agrupar kits por maquinaria específica
        $this->kits_registrados = KitMantenimientoPreventivo::with(['insumo', 'maquinaria.tipoMaquinaria'])
            ->whereNull('deleted_at')
            ->whereNotNull('id_maquinaria')
            ->get()
            ->groupBy('id_maquinaria')
            ->map(function ($items, $maq_id) {
                $maquinaria = Maquinaria::with('tipoMaquinaria')->find($maq_id);
                return [
                    'maquinaria' => $maquinaria,
                    'items' => $items,
                    'total_items' => $items->count(),
                    'obligatorios' => $items->where('es_obligatorio', true)->count(),
                    'opcionales' => $items->where('es_obligatorio', false)->count(),
                ];
            });
    }
    
    public function updatedMaquinariaSeleccionada()
    {
        $this->cargarItemsKit();
        $this->kit_modificado = false;
    }
    // Compatibilidad: si algo aún actualiza el tipo, recargamos igual
    public function updatedTipoMaquinariaSeleccionada()
    {
        $this->cargarItemsKit();
        $this->kit_modificado = false;
    }
    
    public function cargarItemsKit()
    {
        $id = $this->maquinaria_seleccionada ?: null;
        if ($id) {
            $this->items_kit = KitMantenimientoPreventivo::with('insumo')
                ->where('id_maquinaria', $id)
                ->orderBy('es_obligatorio', 'desc')
                ->orderBy('created_at', 'asc')
                ->get();
        } else {
            $this->items_kit = [];
        }
    }
    
    public function registrarKit()
    {
        if (!$this->maquinaria_seleccionada) {
            session()->flash('error', 'Debe seleccionar una maquinaria');
            return;
        }
        
        if (count($this->items_kit) == 0) {
            session()->flash('error', 'El kit debe tener al menos un insumo');
            return;
        }
        
        try {
            $maq = $this->maquinariaSeleccionada;
            $this->kit_modificado = false;
            
            // Limpiar todo para nuevo kit
            $this->limpiarFormulario();
            
            $this->cargarKitsRegistrados();
            $titulo = $maq ? ($maq->modelo . ' (' . optional($maq->tipoMaquinaria)->nombre . ')') : 'Maquinaria';
            session()->flash('message', "Kit para {$titulo} registrado correctamente. Listo para configurar otro kit.");
            
        } catch (\Exception $e) {
            Log::error('Error al registrar kit: ' . $e->getMessage());
            session()->flash('error', 'Error al registrar kit: ' . $e->getMessage());
        }
    }
    
    public function limpiarKit()
    {
        $this->limpiarFormulario();
        session()->flash('message', 'Formulario limpio. Listo para configurar un nuevo kit');
    }
    
    private function limpiarFormulario()
    {
        $this->maquinaria_seleccionada = '';
        $this->tipo_maquinaria_seleccionada = '';
        $this->items_kit = [];
        $this->kit_modificado = false;
        $this->editando_kit = false;
        $this->resetValidation();
    }
    
    public function editarKit($maquinariaId)
    {
        $this->maquinaria_seleccionada = $maquinariaId;
        $this->editando_kit = true;
        $this->cargarItemsKit();
        
        // Emitir evento para cambiar a la pestaña de configuración
        $this->dispatchBrowserEvent('cambiar-tab', ['tab' => 'nuevo-kit']);
    }
    
    public function eliminarKit($maquinariaId)
    {
        try {
            // Soft delete de todos los items del kit por maquinaria
            KitMantenimientoPreventivo::where('id_maquinaria', $maquinariaId)->delete();
            
            $this->cargarKitsRegistrados();
            session()->flash('message', 'Kit eliminado correctamente');
        } catch (\Exception $e) {
            Log::error('Error al eliminar kit: ' . $e->getMessage());
            session()->flash('error', 'Error al eliminar kit: ' . $e->getMessage());
        }
    }
    
    public function abrirModalAgregar()
    {
        $this->resetearFormulario();
        $this->modal_item = true;
    }
    
    public function abrirModalEditar($itemId)
    {
        $item = KitMantenimientoPreventivo::findOrFail($itemId);
        
        $this->item_id = $item->id_kit;
        $this->insumo_id = $item->id_insumo;
        $this->cantidad_requerida = $item->cantidad_requerida;
        $this->es_obligatorio = $item->es_obligatorio;
        
        $this->modal_item = true;
    }
    
    public function cerrarModal()
    {
        $this->modal_item = false;
        $this->resetearFormulario();
    }
    
    public function resetearFormulario()
    {
        $this->item_id = null;
        $this->insumo_id = '';
        $this->cantidad_requerida = 0;
        $this->es_obligatorio = true;
        $this->resetValidation();
    }
    
    public function guardar()
    {
        $this->validate();
        
        try {
            // Validar existencia de maquinaria seleccionada para poder derivar id_tipo_maquinaria (columna NO NULL existente)
            $maquinaria = $this->maquinaria_seleccionada
                ? Maquinaria::find($this->maquinaria_seleccionada)
                : null;

            if (!$maquinaria) {
                session()->flash('error', 'Debe seleccionar una maquinaria válida antes de agregar insumos.');
                return;
            }
            // Verificar si ya existe este insumo en el kit (solo al crear)
            if (!$this->item_id) {
                $existe = KitMantenimientoPreventivo::where('id_maquinaria', $this->maquinaria_seleccionada)
                    ->where('id_insumo', $this->insumo_id)
                    ->exists();
                
                if ($existe) {
                    session()->flash('error', 'Este insumo ya está en el kit. Use editar para modificarlo.');
                    return;
                }
            }
            
            $data = [
                'id_maquinaria' => $this->maquinaria_seleccionada,
                // Mantener id_tipo_maquinaria para compatibilidad y constraint NOT NULL existente
                'id_tipo_maquinaria' => $maquinaria->id_tipo_maquinaria,
                'id_insumo' => $this->insumo_id,
                'cantidad_requerida' => $this->cantidad_requerida,
                'es_obligatorio' => $this->es_obligatorio,
            ];
            
            if ($this->item_id) {
                // Editar
                KitMantenimientoPreventivo::where('id_kit', $this->item_id)->update($data);
                session()->flash('message', 'Item actualizado correctamente');
            } else {
                // Crear
                KitMantenimientoPreventivo::create($data);
                session()->flash('message', 'Item agregado al kit correctamente');
            }
            
            $this->kit_modificado = true;
            $this->cargarItemsKit();
            $this->cerrarModal();
            
        } catch (\Exception $e) {
            Log::error('Error al guardar item del kit: ' . $e->getMessage());
            session()->flash('error', 'Error al guardar: ' . $e->getMessage());
        }
    }
    
    public function eliminar($itemId)
    {
        try {
            $item = KitMantenimientoPreventivo::findOrFail($itemId);
            $item->delete(); // Soft delete
            session()->flash('message', 'Item dado de baja (lógica)');
            $this->kit_modificado = true;
            $this->cargarItemsKit();
        } catch (\Exception $e) {
            Log::error('Error al eliminar item del kit: ' . $e->getMessage());
            session()->flash('error', 'Error al eliminar: ' . $e->getMessage());
        }
    }

    public function restaurar($itemId)
    {
        try {
            $item = KitMantenimientoPreventivo::withTrashed()->findOrFail($itemId);
            if ($item->trashed()) {
                $item->restore();
                session()->flash('message', 'Item restaurado correctamente');
                $this->kit_modificado = true;
            }
            $this->cargarItemsKit();
        } catch (\Exception $e) {
            Log::error('Error al restaurar item del kit: ' . $e->getMessage());
            session()->flash('error', 'Error al restaurar: ' . $e->getMessage());
        }
    }

    public function cargarHistorial()
    {
        if ($this->maquinaria_seleccionada) {
            return KitMantenimientoPreventivo::withTrashed()
                ->with('insumo')
                ->where('id_maquinaria', $this->maquinaria_seleccionada)
                ->orderByDesc('deleted_at')
                ->orderBy('created_at')
                ->get();
        }
        return collect();
    }
    
    public function getMaquinariasProperty()
    {
        return Maquinaria::with('tipoMaquinaria')
            ->orderBy('modelo')
            ->get();
    }
    
    public function getInsumosProperty()
    {
        return Insumo::orderBy('nombre')->get();
    }
    
    public function getMaquinariaSeleccionadaProperty()
    {
        if ($this->maquinaria_seleccionada) {
            return Maquinaria::with('tipoMaquinaria')->find($this->maquinaria_seleccionada);
        }
        return null;
    }

    public function render()
    {
        // Normalizar colecciones para la vista y evitar lógica compleja en Blade
        $itemsCollection = collect($this->items_kit);
        $historial = collect();
        if ($this->maquinaria_seleccionada) {
            $historial = KitMantenimientoPreventivo::withTrashed()
                ->with('insumo')
                ->where('id_maquinaria', $this->maquinaria_seleccionada)
                ->orderByDesc('deleted_at')
                ->orderBy('created_at')
                ->get();
        }

        return view('livewire.configuracion-kits', [
            'maquinarias' => $this->maquinarias,
            'insumos' => $this->insumos,
            'maquinaria_seleccionada_model' => $this->maquinariaSeleccionada,
            // Datos derivados para simplificar Blade
            'items' => $itemsCollection,
            'items_count' => $itemsCollection->count(),
            'items_obligatorios' => $itemsCollection->where('es_obligatorio', true)->count(),
            'items_opcionales' => $itemsCollection->where('es_obligatorio', false)->count(),
            'items_con_stock' => $itemsCollection->filter(function ($it) {
                $stock = optional($it->insumo)->stock;
                return is_numeric($stock) && $stock >= $it->cantidad_requerida;
            })->count(),
            'historial' => $historial,
        ]);
    }
}
