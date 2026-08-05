<?php

namespace App\Http\Livewire;

use App\Models\Cliente;
use App\Models\Venta;
use App\Services\VentaService;
use Livewire\Component;
use Livewire\WithPagination;

class Ventas extends Component
{
    use WithPagination;

    protected $rules = [
        'id_cliente' => 'required|exists:clientes,id_cliente',
        'fecha_desde' => 'required|date',
        'fecha_hasta' => 'required|date|after_or_equal:fecha_desde',
        'observaciones' => 'nullable|string|max:500',
        'busqueda' => 'nullable|string|max:100',
        'obs_edicion' => 'nullable|string|max:500',
        'monto_edicion' => 'nullable|numeric|min:0',
    ];

    protected $messages = [
        'id_cliente.required' => 'El cliente seleccionado no es válido.',
        'id_cliente.exists' => 'El cliente seleccionado no es válido.',
        'fecha_desde.required' => 'La fecha desde es obligatoria.',
        'fecha_desde.date' => 'La fecha desde debe ser una fecha válida.',
        'fecha_hasta.required' => 'La fecha hasta es obligatoria.',
        'fecha_hasta.date' => 'La fecha hasta debe ser una fecha válida.',
        'fecha_hasta.after_or_equal' => 'La fecha hasta debe ser posterior a la fecha desde.',
        'observaciones.max' => 'Las observaciones no pueden superar 500 caracteres.',
        'busqueda.max' => 'La búsqueda no puede superar 100 caracteres.',
        'obs_edicion.max' => 'Las observaciones no pueden superar 500 caracteres.',
        'monto_edicion.numeric' => 'El monto debe ser un número.',
        'monto_edicion.min' => 'El monto no puede ser negativo.',
    ];

    // Control de pestañas
    public $tab_activo = 'historial';

    // Nueva Venta
    public $id_cliente = null;

    public $fecha_desde;

    public $fecha_hasta;

    public $detalle_cargas = [];

    public $total_venta = 0;

    public $observaciones = '';

    // Historial
    public $busqueda = '';

    // Modal detalle
    public $mostrar_modal = false;

    public $venta_seleccionada = null;

    public $detalle_venta = [];

    public $modo_edicion = false;

    public $obs_edicion = '';

    public $monto_edicion = 0;

    public function mount()
    {
        $this->fecha_desde = date('Y-m-d', strtotime('-7 days'));
        $this->fecha_hasta = date('Y-m-d');
    }

    public function buscarCargasPendientes()
    {
        $this->validate([
            'id_cliente' => 'required|exists:clientes,id_cliente',
            'fecha_desde' => 'required|date',
            'fecha_hasta' => 'required|date|after_or_equal:fecha_desde',
        ]);

        try {
            $cargas = VentaService::buscarCargasPendientes(
                $this->id_cliente,
                $this->fecha_desde,
                $this->fecha_hasta
            );

            if (empty($cargas)) {
                $this->detalle_cargas = [];
                $this->total_venta = 0;
                session()->flash('message', 'No se encontraron cargas pendientes.');

                return;
            }

            $this->detalle_cargas = $cargas;
            $this->total_venta = collect($this->detalle_cargas)->sum('subtotal');
            session()->flash('message', 'Cargas cargadas: '.count($this->detalle_cargas));

        } catch (\InvalidArgumentException $e) {
            session()->flash('error', $e->getMessage());
        } catch (\Exception $e) {
            session()->flash('error', 'Error al buscar cargas: '.$e->getMessage());
        }
    }

    public function guardarVenta()
    {
        if (empty($this->detalle_cargas)) {
            session()->flash('error', 'No hay cargas para facturar.');

            return;
        }

        $this->validate([
            'id_cliente' => 'required|exists:clientes,id_cliente',
            'observaciones' => 'nullable|string|max:500',
        ]);

        try {
            $venta = VentaService::registrarVenta(
                $this->id_cliente,
                $this->detalle_cargas,
                $this->total_venta,
                $this->observaciones
            );

            $this->detalle_cargas = [];
            $this->total_venta = 0;
            $this->observaciones = '';
            $this->id_cliente = null;

            session()->flash('message', 'Venta registrada exitosamente. ID: '.$venta->id_recibo);

        } catch (\Exception $e) {
            session()->flash('error', 'Error al guardar la venta: '.$e->getMessage());
        }
    }

    public function cargarVentas()
    {
        return VentaService::listarVentas($this->busqueda, 15);
    }

    public function updatedBusqueda()
    {
        $this->resetPage();
    }

    public function verDetalle($id_recibo)
    {
        $venta = Venta::with(['cliente', 'cargas.categoriaMadera'])
            ->findOrFail($id_recibo);

        $this->venta_seleccionada = $venta;
        $this->obs_edicion = $venta->observaciones;
        $this->monto_edicion = $venta->monto;

        $this->detalle_venta = $venta->cargas->map(function ($carga) {
            return [
                'ticket' => $carga->ticket,
                'fecha_carga' => $carga->fecha_carga,
                'categoria' => $carga->categoriaMadera->nombre ?? 'N/A',
                'peso_kg' => $carga->peso_neto,
                'peso_toneladas' => $carga->pivot->peso_toneladas,
                'precio_unitario' => $carga->pivot->precio_unitario,
                'subtotal' => $carga->pivot->subtotal,
            ];
        })->toArray();

        $this->mostrar_modal = true;
        $this->modo_edicion = false;
    }

    public function activarEdicion()
    {
        $this->modo_edicion = true;
    }

    public function cancelarEdicion()
    {
        if ($this->venta_seleccionada) {
            $this->obs_edicion = $this->venta_seleccionada->observaciones;
            $this->monto_edicion = $this->venta_seleccionada->monto;
        }
        $this->modo_edicion = false;
    }

    public function guardarEdicion()
    {
        if (! $this->venta_seleccionada) {
            session()->flash('error', 'No hay venta seleccionada.');

            return;
        }

        $this->validate([
            'obs_edicion' => 'nullable|string|max:500',
            'monto_edicion' => 'nullable|numeric|min:0',
        ]);

        try {
            VentaService::editarVenta(
                $this->venta_seleccionada->id_recibo,
                $this->obs_edicion,
                $this->monto_edicion
            );

            $this->modo_edicion = false;
            session()->flash('message', 'Venta actualizada exitosamente.');

        } catch (\Exception $e) {
            session()->flash('error', 'Error al actualizar: '.$e->getMessage());
        }
    }

    public function darDeBaja($id_recibo)
    {
        try {
            VentaService::darDeBaja($id_recibo);

            $this->cerrarModal();
            session()->flash('message', 'Venta dada de baja exitosamente. Las cargas están disponibles nuevamente.');

        } catch (\Exception $e) {
            session()->flash('error', 'Error al dar de baja: '.$e->getMessage());
        }
    }

    public function cerrarModal()
    {
        $this->mostrar_modal = false;
        $this->venta_seleccionada = null;
        $this->detalle_venta = [];
        $this->modo_edicion = false;
    }

    public function limpiar()
    {
        $this->id_cliente = null;
        $this->fecha_desde = date('Y-m-d', strtotime('-7 days'));
        $this->fecha_hasta = date('Y-m-d');
        $this->detalle_cargas = [];
        $this->total_venta = 0;
        $this->observaciones = '';
        session()->flash('message', 'Formulario limpiado.');
    }

    public function render()
    {
        $clientes = Cliente::orderBy('razon_social')->get();

        return view('livewire.ventas', [
            'ventas' => $this->cargarVentas(),
            'clientes' => $clientes,
        ]);
    }
}
