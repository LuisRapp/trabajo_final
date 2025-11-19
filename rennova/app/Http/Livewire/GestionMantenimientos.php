<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Mantenimiento;
use App\Models\Maquinaria;
use App\Models\Insumo;
use App\Services\MantenimientoService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GestionMantenimientos extends Component
{
    public $tab_activo = 'ordenes'; // 'ordenes', 'completadas', 'kits'
    
    // Filtros
    public $filtro_estado = '';
    public $filtro_maquinaria = '';
    public $filtro_tipo = '';
    public $filtro_fecha_desde = '';
    public $filtro_fecha_hasta = '';
    
    // Modal aprobar
    public $modal_aprobar = false;
    public $orden_seleccionada = null;
    public $verificacion_stock = null;
    
    // Modal completar
    public $modal_completar = false;
    public $insumos_usados = [];
    public $costo_mano_obra = 0;
    
    // Modal detalle
    public $modal_detalle = false;
    public $detalle_orden = null;
    
    protected $mantenimientoService;
    
    public function boot()
    {
        $this->mantenimientoService = app(MantenimientoService::class);
    }
    
    public function mount()
    {
        $this->filtro_fecha_desde = now()->subMonth()->format('Y-m-d');
        $this->filtro_fecha_hasta = now()->format('Y-m-d');
    }
    
    public function cambiarTab($tab)
    {
        $this->tab_activo = $tab;
        $this->resetearFiltros();
    }
    
    public function resetearFiltros()
    {
        $this->filtro_estado = '';
        $this->filtro_maquinaria = '';
        $this->filtro_tipo = '';
    }
    
    public function abrirModalAprobar($ordenId)
    {
        try {
            $this->orden_seleccionada = Mantenimiento::with(['maquinaria.tipoMaquinaria'])
                ->findOrFail($ordenId);
            
            // Verificar stock disponible
            $this->verificacion_stock = $this->mantenimientoService
                ->verificarStockParaAprobacion($ordenId);
            
            $this->modal_aprobar = true;
        } catch (\Exception $e) {
            session()->flash('error', 'Error al cargar orden: ' . $e->getMessage());
        }
    }
    
    public function cerrarModalAprobar()
    {
        $this->modal_aprobar = false;
        $this->orden_seleccionada = null;
        $this->verificacion_stock = null;
    }
    
    public function aprobarOrden()
    {
        try {
            if (!$this->orden_seleccionada) {
                throw new \Exception('No hay orden seleccionada');
            }
            
            // Verificar stock nuevamente
            $verificacion = $this->mantenimientoService
                ->verificarStockParaAprobacion($this->orden_seleccionada->id);
            
            if (!$verificacion['puede_aprobar']) {
                $faltantes = collect($verificacion['insuficientes'])
                    ->pluck('nombre')
                    ->join(', ');
                
                session()->flash('error', "Stock insuficiente para: {$faltantes}");
                return;
            }
            
            // Aprobar orden
            $this->orden_seleccionada->update([
                'estado' => 'en curso',
                'fecha_inicio' => now(),
            ]);
            
            session()->flash('message', 'Orden aprobada correctamente');
            $this->cerrarModalAprobar();
            
        } catch (\Exception $e) {
            Log::error('Error al aprobar orden: ' . $e->getMessage());
            session()->flash('error', 'Error al aprobar orden: ' . $e->getMessage());
        }
    }
    
    public function abrirModalCompletar($ordenId)
    {
        try {
            $this->orden_seleccionada = Mantenimiento::with([
                'maquinaria.tipoMaquinaria',
                'mantenimientoInsumos.insumo'
            ])->findOrFail($ordenId);
            
            if ($this->orden_seleccionada->estado !== 'en curso') {
                throw new \Exception('Solo se pueden completar órdenes en curso');
            }
            
            // Si es preventivo, cargar kit
            if ($this->orden_seleccionada->tipo_mantenimiento === 'preventivo') {
                $kit = $this->mantenimientoService->obtenerKitPreventivo(
                    $this->orden_seleccionada->maquinaria->tipo_maquinaria_id
                );
                
                // Inicializar insumos con cantidades del kit
                $this->insumos_usados = $kit->map(function($item) {
                    return [
                        'insumo_id' => $item->insumo_id,
                        'cantidad' => $item->cantidad_requerida,
                        'nombre' => $item->insumo->nombre,
                        'stock_disponible' => $item->insumo->stock,
                        'es_obligatorio' => $item->es_obligatorio,
                    ];
                })->toArray();
            } else {
                // Para correctivo, inicializar vacío
                $this->insumos_usados = [];
            }
            
            $this->costo_mano_obra = 0;
            $this->modal_completar = true;
            
        } catch (\Exception $e) {
            session()->flash('error', 'Error al cargar orden: ' . $e->getMessage());
        }
    }
    
    public function agregarInsumo()
    {
        $this->insumos_usados[] = [
            'insumo_id' => '',
            'cantidad' => 0,
            'nombre' => '',
            'stock_disponible' => 0,
            'es_obligatorio' => false,
        ];
    }
    
    public function eliminarInsumo($index)
    {
        if (!$this->insumos_usados[$index]['es_obligatorio']) {
            unset($this->insumos_usados[$index]);
            $this->insumos_usados = array_values($this->insumos_usados);
        }
    }
    
    public function actualizarInsumo($index, $insumoId)
    {
        $insumo = Insumo::find($insumoId);
        if ($insumo) {
            $this->insumos_usados[$index]['nombre'] = $insumo->nombre;
            $this->insumos_usados[$index]['stock_disponible'] = $insumo->stock;
        }
    }
    
    public function cerrarModalCompletar()
    {
        $this->modal_completar = false;
        $this->orden_seleccionada = null;
        $this->insumos_usados = [];
        $this->costo_mano_obra = 0;
    }
    
    public function completarMantenimiento()
    {
        // Validaciones
        $this->validate([
            'costo_mano_obra' => 'required|numeric|min:0',
            'insumos_usados.*.insumo_id' => 'required|exists:insumos,id',
            'insumos_usados.*.cantidad' => 'required|numeric|min:0.01',
        ], [
            'costo_mano_obra.required' => 'El costo de mano de obra es requerido',
            'costo_mano_obra.min' => 'El costo debe ser mayor o igual a 0',
            'insumos_usados.*.insumo_id.required' => 'Debe seleccionar un insumo',
            'insumos_usados.*.cantidad.required' => 'Debe especificar la cantidad',
            'insumos_usados.*.cantidad.min' => 'La cantidad debe ser mayor a 0',
        ]);
        
        try {
            // Preparar datos de insumos
            $insumosData = collect($this->insumos_usados)->map(function($item) {
                return [
                    'insumo_id' => $item['insumo_id'],
                    'cantidad' => $item['cantidad'],
                ];
            })->toArray();
            
            // Completar mantenimiento usando el servicio
            $this->mantenimientoService->completarMantenimiento(
                $this->orden_seleccionada->id,
                $insumosData,
                $this->costo_mano_obra
            );
            
            session()->flash('message', 'Mantenimiento completado correctamente');
            $this->cerrarModalCompletar();
            
        } catch (\Exception $e) {
            Log::error('Error al completar mantenimiento: ' . $e->getMessage());
            session()->flash('error', 'Error al completar: ' . $e->getMessage());
        }
    }
    
    public function verDetalle($ordenId)
    {
        try {
            $this->detalle_orden = Mantenimiento::with([
                'maquinaria.tipoMaquinaria',
                'mantenimientoInsumos.insumo'
            ])->findOrFail($ordenId);
            
            $this->modal_detalle = true;
        } catch (\Exception $e) {
            session()->flash('error', 'Error al cargar detalle: ' . $e->getMessage());
        }
    }
    
    public function cerrarModalDetalle()
    {
        $this->modal_detalle = false;
        $this->detalle_orden = null;
    }
    
    public function getOrdenesProperty()
    {
        $query = Mantenimiento::with(['maquinaria.tipoMaquinaria'])
            ->whereBetween('created_at', [
                $this->filtro_fecha_desde ?: now()->subYear(),
                $this->filtro_fecha_hasta ?: now()
            ]);
        
        if ($this->filtro_estado) {
            $query->where('estado', $this->filtro_estado);
        }
        
        if ($this->filtro_maquinaria) {
            $query->where('maquinaria_id', $this->filtro_maquinaria);
        }
        
        if ($this->filtro_tipo) {
            $query->where('tipo_mantenimiento', $this->filtro_tipo);
        }
        
        // Filtrar según la pestaña activa
        if ($this->tab_activo === 'ordenes') {
            $query->whereIn('estado', ['programado', 'en curso']);
        } elseif ($this->tab_activo === 'completadas') {
            $query->where('estado', 'completado');
        }
        
        return $query->orderBy('created_at', 'desc')->get();
    }
    
    public function getMaquinariasProperty()
    {
        return Maquinaria::with('tipoMaquinaria')
            ->where('estado', 'activo')
            ->orderBy('modelo')
            ->get();
    }
    
    public function getInsumosDisponiblesProperty()
    {
        return Insumo::orderBy('nombre')->get();
    }

    public function render()
    {
        return view('livewire.gestion-mantenimientos', [
            'ordenes' => $this->ordenes,
            'maquinarias' => $this->maquinarias,
            'insumos_disponibles' => $this->insumosDisponibles,
        ]);
    }
}
