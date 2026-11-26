<?php

namespace App\Http\Livewire;

use App\Http\Livewire\Traits\MensajesErrorUsuario;
use App\Models\Insumo;
use App\Models\LoteInventario;
use App\Models\Proveedor;
use App\Services\InventarioService;
use Livewire\Component;
use Livewire\WithPagination;

class GestionStock extends Component
{
    use MensajesErrorUsuario;
    use WithPagination;

    protected $paginationTheme = 'tailwind';

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

    public $mensajePanel = null;

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
        'precio_unitario' => 'nullable|numeric|min:0',
        'numero_factura' => 'nullable|string|max:100',
        'tipo_movimiento' => 'required|in:compra,ajuste_entrada,ajuste_salida,devolucion',
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

    public function getInsumosProperty()
    {
        return Insumo::orderBy('nombre')->get();
    }

    public function getProveedoresProperty()
    {
        return Proveedor::orderBy('razon_social')->get();
    }

    public function render()
    {
        $lotes = $this->obtenerLotes();
        $estadisticas = $this->calcularEstadisticas();

        return view('livewire.gestion-stock', [
            'lotes' => $lotes,
            'insumos' => $this->insumos,
            'proveedores' => $this->proveedores,
            'estadisticas' => $estadisticas,
        ]);
    }

    private function obtenerLotes()
    {
        return InventarioService::consultaLotesFiltrados($this->filtrosStock())->paginate(20);
    }

    /**
     * @return array{insumo: ?string, proveedor: ?string, tipo: ?string, estado: string, desde: ?string, hasta: ?string}
     */
    private function filtrosStock(): array
    {
        return [
            'insumo' => $this->filtro_insumo ?: null,
            'proveedor' => $this->filtro_proveedor ?: null,
            'tipo' => $this->filtro_tipo ?: null,
            'estado' => $this->filtro_estado,
            'desde' => $this->filtro_fecha_inicio ?: null,
            'hasta' => $this->filtro_fecha_fin ?: null,
        ];
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
            'lotes_proximos_agotar' => $lotes->filter(fn ($l) => InventarioService::estaProximoAgotar($l))->count(),
        ];
    }

    public function abrirModal()
    {
        $this->resetearFormulario();
        $this->mensajePanel = null;
        $this->mostrarModal = true;
        $this->modoEdicion = false;
    }

    public function cerrarModal()
    {
        $this->mostrarModal = false;
        $this->mensajePanel = null;
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
        $this->authorize('crear-gestion-stock');

        // El precio solo tiene sentido en los ingresos: el ajuste de salida
        // consume por FIFO y no fija precio.
        $reglas = $this->rules;

        if (! in_array($this->tipo_movimiento, ['ajuste_salida', 'devolucion'], true)) {
            $reglas['precio_unitario'] = 'required|numeric|min:0.01';
        }

        $this->validate($reglas);

        try {
            if (in_array($this->tipo_movimiento, ['ajuste_salida', 'devolucion'], true)) {
                $resultado = \App\Services\InventarioService::registrarSalida(
                    $this->id_insumo,
                    $this->cantidad,
                    $this->generarMotivo(),
                    $this->fecha_compra
                );

                $cantidad = collect($resultado['movimientos'])->sum('cantidad');
                $etiqueta = $this->tipo_movimiento === 'devolucion' ? 'Devolución registrada' : 'Salida registrada';
                session()->flash('message', $etiqueta.': se descontaron '.number_format((float) $cantidad, 2, ',', '.').'.');
                session()->flash('alert-type', 'success');

                $this->cerrarModal();
                $this->resetPage();

                return;
            }

            $metadata = [
                'id_proveedor' => $this->id_proveedor,
                'numero_factura' => $this->numero_factura,
                'tipo_movimiento' => $this->tipo_movimiento,
                'observaciones' => $this->observaciones,
                'motivo' => $this->generarMotivo(),
            ];

            $resultado = InventarioService::registrarEntrada(
                $this->id_insumo,
                $this->cantidad,
                $this->precio_unitario,
                $metadata,
                $this->fecha_compra
            );

            session()->flash('message', 'Lote de inventario registrado correctamente');
            session()->flash('alert-type', 'success');

            $this->cerrarModal();
            $this->resetPage();

        } catch (\Exception $e) {
            \Log::error('Error al guardar lote: '.$e->getMessage());
            \Log::error($e->getTraceAsString());
            $this->mensajePanel = $e instanceof \DomainException
                ? $e->getMessage()
                : $this->mensajeErrorUsuario($e, 'registrar el lote de inventario');
        }
    }

    private function generarMotivo()
    {
        $motivos = [
            'compra' => 'Compra',
            'ajuste_entrada' => 'Ajuste de Entrada',
            'devolucion' => 'Devolución a proveedor',
            'ajuste_salida' => 'Ajuste de Salida',
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
        $this->authorize('ver-gestion-stock');

        return $this->redirect(route('reportes.stock', array_filter($this->filtrosStock(), fn ($valor) => $valor !== null && $valor !== '')));
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
