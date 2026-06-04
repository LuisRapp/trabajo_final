<?php

namespace App\Http\Livewire;

use App\Models\Insumo;
use App\Models\LoteInventario;
use App\Models\Proveedor;
use App\Services\InventarioService;
use Livewire\Component;
use Livewire\WithPagination;

class GestionStock extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    // Propiedades de formulario
    public $id_insumo;

    public $id_proveedor;

    public $cantidad;

    public $precio_unitario;

    public $numero_factura;

    public $tipo_movimiento = 'compra';

    public $observaciones;

    public $fecha_compra;

    // Propiedades de UI
    public $mostrarModal = false;

    public $modoEdicion = false;

    public $loteSeleccionado = null;

    // Filtros
    public $filtro_insumo = '';

    public $filtro_proveedor = '';

    public $filtro_fecha_inicio = '';

    public $filtro_fecha_fin = '';

    public $filtro_tipo = '';

    public $filtro_estado = 'disponibles'; // disponibles, agotados, todos

    protected $rules = [
        'id_insumo' => 'required|exists:insumos,id_insumo',
        'id_proveedor' => 'nullable|exists:proveedores,id_proveedor',
        'cantidad' => 'required|numeric|min:0.01',
        'precio_unitario' => 'required|numeric|min:0.01',
        'numero_factura' => 'nullable|string|max:100',
        'tipo_movimiento' => 'required|in:compra,ajuste_entrada,devolucion',
        'observaciones' => 'nullable|string|max:500',
        'fecha_compra' => 'required|date',
    ];

    protected $messages = [
        'id_insumo.required' => 'Debe seleccionar un insumo',
        'cantidad.required' => 'La cantidad es obligatoria',
        'cantidad.min' => 'La cantidad debe ser mayor a 0',
        'precio_unitario.required' => 'El precio unitario es obligatorio',
        'precio_unitario.min' => 'El precio debe ser mayor a 0',
        'fecha_compra.required' => 'La fecha de compra es obligatoria',
        'tipo_movimiento.in' => 'Tipo de movimiento inválido',
    ];

    public function mount()
    {
        $this->fecha_compra = now()->format('Y-m-d');
        $this->filtro_fecha_inicio = now()->subMonths(1)->format('Y-m-d');
        $this->filtro_fecha_fin = now()->format('Y-m-d');
    }

    public function render()
    {
        $lotes = $this->obtenerLotes();
        $insumos = Insumo::orderBy('nombre')->get();
        $proveedores = Proveedor::orderBy('razon_social')->get();

        // Estadísticas
        $estadisticas = $this->calcularEstadisticas();

        return view('livewire.gestion-stock', [
            'lotes' => $lotes,
            'insumos' => $insumos,
            'proveedores' => $proveedores,
            'estadisticas' => $estadisticas,
        ]);
    }

    private function obtenerLotes()
    {
        $query = LoteInventario::with(['insumo', 'proveedor']);

        // Filtro por estado
        if ($this->filtro_estado === 'disponibles') {
            $query->disponibles();
        } elseif ($this->filtro_estado === 'agotados') {
            $query->agotados();
        }

        // Filtro por insumo
        if ($this->filtro_insumo) {
            $query->where('id_insumo', $this->filtro_insumo);
        }

        // Filtro por proveedor
        if ($this->filtro_proveedor) {
            $query->where('id_proveedor', $this->filtro_proveedor);
        }

        // Filtro por tipo de movimiento
        if ($this->filtro_tipo) {
            $query->where('tipo_movimiento', $this->filtro_tipo);
        }

        // Filtro por rango de fechas
        if ($this->filtro_fecha_inicio && $this->filtro_fecha_fin) {
            $query->entreFechas($this->filtro_fecha_inicio, $this->filtro_fecha_fin);
        }

        return $query->ordenFifo()->paginate(20);
    }

    private function calcularEstadisticas()
    {
        $query = LoteInventario::disponibles();

        if ($this->filtro_insumo) {
            $query->where('id_insumo', $this->filtro_insumo);
        }

        $lotes = $query->get();

        return [
            'total_lotes' => $lotes->count(),
            'stock_total' => $lotes->sum('cantidad_disponible'),
            'valor_inventario' => $lotes->sum(function ($lote) {
                return $lote->cantidad_disponible * $lote->precio_unitario;
            }),
            'lotes_proximos_agotar' => $lotes->filter(fn ($l) => $l->estaProximoAgotar())->count(),
        ];
    }

    public function abrirModal()
    {
        $this->resetearFormulario();
        $this->mostrarModal = true;
        $this->modoEdicion = false;
    }

    public function cerrarModal()
    {
        $this->mostrarModal = false;
        $this->resetearFormulario();
        $this->resetValidation();
    }

    private function resetearFormulario()
    {
        $this->id_insumo = '';
        $this->id_proveedor = '';
        $this->cantidad = '';
        $this->precio_unitario = '';
        $this->numero_factura = '';
        $this->tipo_movimiento = 'compra';
        $this->observaciones = '';
        $this->fecha_compra = now()->format('Y-m-d');
        $this->loteSeleccionado = null;
    }

    public function guardar()
    {
        $this->validate();

        try {
            \Log::info('=== INICIO GUARDAR LOTE ===');
            \Log::info('Datos recibidos:', [
                'id_insumo' => $this->id_insumo,
                'cantidad' => $this->cantidad,
                'precio_unitario' => $this->precio_unitario,
                'fecha_compra' => $this->fecha_compra,
            ]);

            $metadata = [
                'id_proveedor' => $this->id_proveedor,
                'numero_factura' => $this->numero_factura,
                'tipo_movimiento' => $this->tipo_movimiento,
                'observaciones' => $this->observaciones,
                'motivo' => $this->generarMotivo(),
            ];

            \Log::info('Metadata:', $metadata);

            $resultado = InventarioService::registrarEntrada(
                $this->id_insumo,
                $this->cantidad,
                $this->precio_unitario,
                $metadata,
                $this->fecha_compra
            );

            \Log::info('Resultado:', [
                'lote_id' => $resultado['lote']->id_lote_inventario,
                'cantidad_inicial' => $resultado['lote']->cantidad_inicial,
                'cantidad_disponible' => $resultado['lote']->cantidad_disponible,
            ]);

            session()->flash('message', 'Lote de inventario registrado correctamente');
            session()->flash('alert-type', 'success');

            $this->cerrarModal();
            $this->resetPage();

        } catch (\Exception $e) {
            \Log::error('Error al guardar lote: '.$e->getMessage());
            \Log::error($e->getTraceAsString());
            session()->flash('message', 'Error al registrar el lote: '.$e->getMessage());
            session()->flash('alert-type', 'danger');
        }
    }

    private function generarMotivo()
    {
        $motivos = [
            'compra' => 'Compra',
            'ajuste_entrada' => 'Ajuste de Entrada',
            'devolucion' => 'Devolución',
        ];

        $motivo = $motivos[$this->tipo_movimiento] ?? 'Entrada';

        if ($this->numero_factura) {
            $motivo .= ' - Factura '.$this->numero_factura;
        }

        return $motivo;
    }

    public function verDetalle($idLote)
    {
        $this->loteSeleccionado = LoteInventario::with(['insumo', 'proveedor', 'movimientos'])
            ->findOrFail($idLote);
    }

    public function cerrarDetalle()
    {
        $this->loteSeleccionado = null;
    }

    public function limpiarFiltros()
    {
        $this->filtro_insumo = '';
        $this->filtro_proveedor = '';
        $this->filtro_tipo = '';
        $this->filtro_estado = 'disponibles';
        $this->filtro_fecha_inicio = now()->subMonths(1)->format('Y-m-d');
        $this->filtro_fecha_fin = now()->format('Y-m-d');
        $this->resetPage();
    }

    public function exportarReporte()
    {
        // TODO: Implementar exportación a Excel/PDF
        session()->flash('message', 'Funcionalidad de exportación en desarrollo');
        session()->flash('alert-type', 'info');
    }

    public function updatingFiltroInsumo()
    {
        $this->resetPage();
    }

    public function updatingFiltroProveedor()
    {
        $this->resetPage();
    }

    public function updatingFiltroEstado()
    {
        $this->resetPage();
    }

    public function updatingFiltroTipo()
    {
        $this->resetPage();
    }
}
