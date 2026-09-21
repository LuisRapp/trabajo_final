<?php

namespace App\Http\Livewire;

use App\Http\Livewire\Traits\MensajesErrorUsuario;
use App\Services\MantenimientoService;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class GestionMantenimientos extends Component
{
    use MensajesErrorUsuario;

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
            $this->orden_seleccionada = $this->mantenimientoService
                ->obtenerOrdenParaAprobar((int) $ordenId);

            // Verificar stock disponible
            $this->verificacion_stock = $this->mantenimientoService
                ->verificarStockParaAprobacion((int) $ordenId);

            $this->modal_aprobar = true;
        } catch (\Exception $e) {
            session()->flash('error', $this->mensajeErrorUsuario($e, 'cargar la orden'));
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
            if (! $this->orden_seleccionada) {
                throw new \Exception('No hay orden seleccionada');
            }

            $resultado = $this->mantenimientoService
                ->aprobarMantenimiento($this->orden_seleccionada->id_mantenimiento);

            if (! $resultado['success']) {
                if (! empty($resultado['insumos_insuficientes'])) {
                    $faltantes = collect($resultado['insumos_insuficientes'])
                        ->pluck('insumo')
                        ->join(', ');

                    session()->flash('error', "Stock insuficiente para: {$faltantes}");

                    return;
                }

                session()->flash('error', $resultado['message']);

                return;
            }

            session()->flash('message', 'Orden aprobada correctamente');
            $this->cerrarModalAprobar();

        } catch (\Exception $e) {
            Log::error('Error al aprobar orden: '.$e->getMessage());
            session()->flash('error', $this->mensajeErrorUsuario($e, 'aprobar la orden'));
        }
    }

    public function abrirModalCompletar($ordenId)
    {
        try {
            $this->orden_seleccionada = $this->mantenimientoService
                ->obtenerOrdenParaCompletar((int) $ordenId);

            if ($this->orden_seleccionada->estado !== 'en curso') {
                throw new \Exception('Solo se pueden completar órdenes en curso');
            }

            // Si es preventivo, cargar kit
            if ($this->orden_seleccionada->tipoMantenimiento
                && $this->mantenimientoService->esTipoPreventivo($this->orden_seleccionada->tipoMantenimiento)) {
                $kit = $this->mantenimientoService->obtenerKitPreventivo(
                    $this->orden_seleccionada->maquinaria->id_tipo_maquinaria
                );

                // Inicializar insumos con cantidades del kit
                $this->insumos_usados = $kit->map(function ($item) {
                    return [
                        'insumo_id' => $item->id_insumo,
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
            session()->flash('error', $this->mensajeErrorUsuario($e, 'cargar la orden'));
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
        if (! $this->insumos_usados[$index]['es_obligatorio']) {
            unset($this->insumos_usados[$index]);
            $this->insumos_usados = array_values($this->insumos_usados);
        }
    }

    public function actualizarInsumo($index, $insumoId)
    {
        $insumo = $this->mantenimientoService->obtenerInsumo((int) $insumoId);
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
            'insumos_usados.*.insumo_id' => 'required|exists:insumos,id_insumo',
            'insumos_usados.*.cantidad' => 'required|numeric|min:0.01',
        ], [
            'costo_mano_obra.required' => 'El costo de mano de obra es requerido',
            'costo_mano_obra.min' => 'El costo debe ser mayor o igual a 0',
            'insumos_usados.*.insumo_id.required' => 'Debe seleccionar un insumo',
            'insumos_usados.*.insumo_id.exists' => 'El insumo seleccionado no es válido',
            'insumos_usados.*.cantidad.required' => 'Debe especificar la cantidad',
            'insumos_usados.*.cantidad.min' => 'La cantidad debe ser mayor a 0',
        ]);

        try {
            // Preparar datos de insumos
            $insumosData = collect($this->insumos_usados)->map(function ($item) {
                return [
                    'id_insumo' => $item['insumo_id'],
                    'cantidad_utilizada' => $item['cantidad'],
                ];
            })->toArray();

            // Completar mantenimiento usando el servicio
            $resultado = $this->mantenimientoService->completarMantenimiento(
                $this->orden_seleccionada->id_mantenimiento,
                $insumosData,
                (float) $this->costo_mano_obra
            );

            if (! $resultado['success']) {
                session()->flash('error', $resultado['message']);

                return;
            }

            session()->flash('message', 'Mantenimiento completado correctamente');
            $this->cerrarModalCompletar();

        } catch (\Exception $e) {
            Log::error('Error al completar mantenimiento: '.$e->getMessage());
            session()->flash('error', $this->mensajeErrorUsuario($e, 'completar la orden'));
        }
    }

    public function verDetalle($ordenId)
    {
        try {
            $this->detalle_orden = $this->mantenimientoService
                ->obtenerOrdenParaDetalle((int) $ordenId);

            $this->modal_detalle = true;
        } catch (\Exception $e) {
            session()->flash('error', $this->mensajeErrorUsuario($e, 'cargar el detalle'));
        }
    }

    public function cerrarModalDetalle()
    {
        $this->modal_detalle = false;
        $this->detalle_orden = null;
    }

    public function getOrdenesProperty()
    {
        return $this->mantenimientoService->listarOrdenesGestion([
            'estado' => $this->filtro_estado,
            'maquinaria' => $this->filtro_maquinaria,
            'tipo' => $this->filtro_tipo,
            'fecha_desde' => $this->filtro_fecha_desde,
            'fecha_hasta' => $this->filtro_fecha_hasta,
        ], $this->tab_activo);
    }

    public function getMaquinariasProperty()
    {
        return $this->mantenimientoService->obtenerMaquinariasActivasParaFiltro();
    }

    public function getInsumosDisponiblesProperty()
    {
        return $this->mantenimientoService->obtenerInsumosParaCierre();
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
