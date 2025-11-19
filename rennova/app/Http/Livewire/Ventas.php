<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Cliente;
use App\Models\Carga;
use App\Models\Venta;
use Illuminate\Support\Facades\DB;

class Ventas extends Component
{
    // Control de pestañas
    public $tab_activo = 'nuevo';
    
    // Nueva Venta
    public $id_cliente = null;
    public $fecha_desde;
    public $fecha_hasta;
    public $detalle_cargas = [];
    public $total_venta = 0;
    public $observaciones = '';
    
    // Historial
    public $ventas = [];
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
        $this->cargarVentas();
    }

    public function buscarCargasPendientes()
    {
        if (empty($this->id_cliente)) {
            session()->flash('error', 'Seleccione un cliente.');
            return;
        }
        if (empty($this->fecha_desde) || empty($this->fecha_hasta)) {
            session()->flash('error', 'Seleccione el rango de fechas.');
            return;
        }

        $cliente = Cliente::find($this->id_cliente);
        if (!$cliente) {
            session()->flash('error', 'Cliente no encontrado.');
            return;
        }

        $nombreCliente = $cliente->razon_social;

        $query = Carga::query()
            ->select([
                'cargas.id_carga',
                'cargas.fecha_carga',
                'cargas.ticket',
                'cargas.peso_neto',
                'cargas.id_categoria_madera',
                'cargas.destino',
                DB::raw('cat.nombre as categoria'),
                DB::raw('ROUND(cargas.peso_neto / 1000.0, 3) as peso_toneladas'),
                DB::raw('COALESCE(ccp.precio, 0) as precio_unitario'),
                DB::raw('ROUND((cargas.peso_neto / 1000.0) * COALESCE(ccp.precio, 0), 2) as subtotal'),
            ])
            ->join('categoria_maderas as cat', 'cat.id_categoria_madera', '=', 'cargas.id_categoria_madera')
            ->leftJoin('categoria_cliente_precio as ccp', function($join) {
                $join->on('ccp.categoria_id', '=', 'cargas.id_categoria_madera')
                    ->where('ccp.cliente_id', '=', $this->id_cliente)
                    ->whereColumn('ccp.fecha_desde', '<=', 'cargas.fecha_carga')
                    ->where(function($q) {
                        $q->whereNull('ccp.fecha_hasta')
                          ->orWhereColumn('ccp.fecha_hasta', '>=', 'cargas.fecha_carga');
                    });
            })
            ->where('cargas.destino', $nombreCliente)
            ->where('cargas.estado', 'pendiente')
            ->whereBetween('cargas.fecha_carga', [$this->fecha_desde, $this->fecha_hasta])
            ->orderBy('cargas.fecha_carga');

        $rows = $query->get();

        if ($rows->isEmpty()) {
            $this->detalle_cargas = [];
            $this->total_venta = 0;
            session()->flash('message', 'No se encontraron cargas pendientes.');
            return;
        }

        $this->detalle_cargas = $rows->map(function($r) {
            return [
                'id_carga' => $r->id_carga,
                'fecha_carga' => $r->fecha_carga,
                'ticket' => $r->ticket,
                'categoria' => $r->categoria,
                'peso_kg' => (float) $r->peso_neto,
                'peso_toneladas' => (float) $r->peso_toneladas,
                'precio_unitario' => (float) $r->precio_unitario,
                'subtotal' => (float) $r->subtotal,
            ];
        })->toArray();

        $this->total_venta = collect($this->detalle_cargas)->sum('subtotal');
        session()->flash('message', 'Cargas cargadas: ' . count($this->detalle_cargas));
    }

    public function guardarVenta()
    {
        if (empty($this->detalle_cargas)) {
            session()->flash('error', 'No hay cargas para facturar.');
            return;
        }

        if (empty($this->id_cliente)) {
            session()->flash('error', 'Cliente no encontrado.');
            return;
        }

        DB::beginTransaction();
        try {
            $venta = Venta::create([
                'id_cliente' => $this->id_cliente,
                'fecha_emision' => now()->toDateString(),
                'monto' => $this->total_venta,
                'observaciones' => $this->observaciones,
                'activo' => true,
            ]);

            foreach ($this->detalle_cargas as $detalle) {
                $venta->cargas()->attach($detalle['id_carga'], [
                    'precio_unitario' => $detalle['precio_unitario'],
                    'peso_toneladas' => $detalle['peso_toneladas'],
                    'subtotal' => $detalle['subtotal'],
                ]);

                Carga::where('id_carga', $detalle['id_carga'])
                    ->update(['estado' => 'facturada']);
            }

            DB::commit();

            $this->detalle_cargas = [];
            $this->total_venta = 0;
            $this->observaciones = '';
            $this->id_cliente = null;
            
            $this->cargarVentas(); // Refrescar historial

            session()->flash('message', 'Venta registrada exitosamente. ID: ' . $venta->id_recibo);

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error al guardar la venta: ' . $e->getMessage());
        }
    }

    public function cargarVentas()
    {
        $query = Venta::with(['cliente', 'cargas'])
            ->orderBy('fecha_emision', 'desc')
            ->orderBy('id_recibo', 'desc');

        if ($this->busqueda) {
            $busq = $this->busqueda;
            $query->where(function ($q) use ($busq) {
                $q->whereHas('cliente', function ($qc) use ($busq) {
                    $qc->where('razon_social', 'ILIKE', '%' . $busq . '%');
                })
                ->orWhere('id_recibo', 'LIKE', '%' . $busq . '%')
                ->orWhereRaw("CAST(monto AS TEXT) ILIKE ?", ['%' . $busq . '%']);
            });
        }

        $this->ventas = $query->get();
    }

    public function updatedBusqueda()
    {
        $this->cargarVentas();
    }

    public function verDetalle($id_recibo)
    {
        $venta = Venta::with(['cliente', 'cargas.categoriaMadera'])
            ->findOrFail($id_recibo);
        
        $this->venta_seleccionada = $venta;
        $this->obs_edicion = $venta->observaciones;
        $this->monto_edicion = $venta->monto;
        
        $this->detalle_venta = $venta->cargas->map(function($carga) {
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
        if (!$this->venta_seleccionada) {
            session()->flash('error', 'No hay venta seleccionada.');
            return;
        }
        
        try {
            $this->venta_seleccionada->update([
                'observaciones' => $this->obs_edicion,
                'monto' => $this->monto_edicion,
            ]);
            
            $this->cargarVentas();
            $this->modo_edicion = false;
            session()->flash('message', 'Venta actualizada exitosamente.');
            
        } catch (\Exception $e) {
            session()->flash('error', 'Error al actualizar: ' . $e->getMessage());
        }
    }

    public function darDeBaja($id_recibo)
    {
        DB::beginTransaction();
        try {
            $venta = Venta::with('cargas')->findOrFail($id_recibo);
            
            // Marcar la venta como inactiva
            $venta->update(['activo' => false]);
            
            // Retornar las cargas al estado "pendiente"
            foreach ($venta->cargas as $carga) {
                $carga->update(['estado' => 'pendiente']);
            }
            
            DB::commit();
            
            $this->cargarVentas();
            $this->cerrarModal();
            session()->flash('message', 'Venta dada de baja exitosamente. Las cargas están disponibles nuevamente.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error al dar de baja: ' . $e->getMessage());
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
        return view('livewire.ventas', compact('clientes'));
    }
}
