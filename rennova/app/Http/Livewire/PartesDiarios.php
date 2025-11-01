<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\ParteDiario;
use App\Models\Lote;
use App\Models\Empleado;
use App\Models\Carga;
use App\Models\Chofer;
use App\Models\Insumo;
use App\Models\MovimientoStock;
use App\Models\Recibo;
use App\Models\CategoriaMadera;
use App\Models\Cliente;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PartesDiarios extends Component
{
    // Parte Diario Principal
    public $partes = [];
    public $parte_id;
    public $id_lote;
    public $fecha;
    public $actividad_realizada;
    public $es_dia_caido = false;
    public $motivo_dia_caido;
    public $observaciones;
    public $busqueda = '';
    
    // Catálogos
    public $lotes = [];
    public $empleados = [];
    public $choferes = [];
    public $insumos = [];
    public $categorias_madera = [];
    public $clientes = [];
    
    // Detalles de Cargas (Modo Destajo)
    public $cargas = [];
    public $carga_id_categoria_madera;
    public $carga_ticket;
    public $carga_peso_bruto;
    public $carga_tara;
    public $carga_peso_neto;
    public $carga_id_chofer;
    public $carga_destino; // Este será el id_cliente
    public $carga_empleados = []; // Array de IDs de empleados para esta carga
    public $total_toneladas = 0;
    
    // Búsqueda dinámica para autocomplete
    public $busqueda_chofer = '';
    public $busqueda_cliente = '';
    
    // Detalles de Jornales (Modo Día Caído)
    public $jornales = [];
    public $jornal_id_empleado;
    public $jornal_observaciones;
    
    // Detalles de Movimientos de Insumos
    public $movimientos = [];
    public $movimiento_id_insumo;
    public $movimiento_tipo = 'entrada';
    public $movimiento_cantidad;
    public $movimiento_motivo = 'Producción'; // Nuevo campo
    public $movimiento_observaciones; // Nuevo campo

    protected function rules()
    {
        $rules = [
            'id_lote' => 'required|exists:lotes,id_lote',
            'fecha' => 'required|date',
            'actividad_realizada' => 'required|string|max:255',
            'observaciones' => 'nullable|string',
        ];
        
        if ($this->es_dia_caido) {
            $rules['motivo_dia_caido'] = 'required|string|max:500';
        }
        
        return $rules;
    }

    public function mount()
    {
        $this->lotes = Lote::where('estado', 'activo')->orderBy('propietario')->get();
        $this->empleados = Empleado::with('rolLaboral')->whereNull('fecha_fin_actividades')->orderBy('apellido')->get();
        $this->choferes = Chofer::where('estado', true)->orderBy('apellido')->get();
        $this->insumos = Insumo::orderBy('nombre')->get();
        $this->categorias_madera = CategoriaMadera::orderBy('nombre')->get();
        $this->clientes = Cliente::orderBy('razon_social')->get();
        $this->cargarPartes();
    }
    
    public function updatedCargaPesoBruto()
    {
        $this->calcularPesoNeto();
    }
    
    public function updatedCargaTara()
    {
        $this->calcularPesoNeto();
    }
    
    private function calcularPesoNeto()
    {
        if ($this->carga_peso_bruto && $this->carga_tara) {
            $this->carga_peso_neto = $this->carga_peso_bruto - $this->carga_tara;
        } else {
            $this->carga_peso_neto = null;
        }
    }
    
    public function getChoferesFiltradosProperty()
    {
        if (empty($this->busqueda_chofer)) {
            return $this->choferes;
        }
        
        $busq = strtolower($this->busqueda_chofer);
        return $this->choferes->filter(function($chofer) use ($busq) {
            $nombre_completo = strtolower($chofer->apellido . ' ' . $chofer->nombre);
            return str_contains($nombre_completo, $busq);
        });
    }
    
    public function getClientesFiltradosProperty()
    {
        if (empty($this->busqueda_cliente)) {
            return $this->clientes;
        }
        
        $busq = strtolower($this->busqueda_cliente);
        return $this->clientes->filter(function($cliente) use ($busq) {
            return str_contains(strtolower($cliente->razon_social), $busq);
        });
    }

    public function render()
    {
        $this->cargarPartes();
        return view('livewire.partes-diarios');
    }

    public function cargarPartes()
    {
        $query = ParteDiario::with('lote');

        if ($this->busqueda) {
            $busq = $this->busqueda;
            $query->where(function($q) use ($busq) {
                $q->whereDate('fecha', $busq)
                  ->orWhereHas('lote', function($ql) use ($busq) {
                      $ql->where('propietario', 'ILIKE', '%' . $busq . '%')
                         ->orWhere('ubicacion', 'ILIKE', '%' . $busq . '%');
                  });
            });
        }

        $this->partes = $query->orderBy('fecha', 'desc')->orderBy('id_parte_diario', 'desc')->get();
    }

    public function updatedBusqueda()
    {
        $this->cargarPartes();
    }
    
    public function updatedEsDiaCaido()
    {
        // Limpiar datos de la sección no activa
        if ($this->es_dia_caido) {
            $this->cargas = [];
            $this->carga_empleados = [];
            $this->total_toneladas = 0;
        } else {
            $this->jornales = [];
            $this->motivo_dia_caido = '';
        }
    }
    
    // ============ GESTIÓN DE CARGAS (DESTAJO) ============
    
    public function agregarCarga()
    {
        $this->validate([
            'carga_id_categoria_madera' => 'required|exists:categoria_maderas,id_categoria_madera',
            'carga_ticket' => 'required|string|max:100',
            'carga_peso_bruto' => 'required|numeric|min:0.01',
            'carga_tara' => 'required|numeric|min:0',
            'carga_peso_neto' => 'required|numeric|min:0.01',
            'carga_id_chofer' => 'required|exists:choferes,id_chofer',
            'carga_destino' => 'required|exists:clientes,id_cliente',
            'carga_empleados' => 'required|array|min:1',
        ], [
            'carga_id_categoria_madera.required' => 'La categoría de madera es obligatoria',
            'carga_ticket.required' => 'El número de ticket es obligatorio',
            'carga_peso_bruto.required' => 'El peso bruto es obligatorio',
            'carga_peso_bruto.min' => 'El peso bruto debe ser mayor a 0',
            'carga_tara.required' => 'La tara es obligatoria',
            'carga_peso_neto.required' => 'El peso neto es obligatorio',
            'carga_peso_neto.min' => 'El peso neto debe ser mayor a 0',
            'carga_id_chofer.required' => 'El chofer es obligatorio',
            'carga_destino.required' => 'El destino (cliente) es obligatorio',
            'carga_empleados.required' => 'Debe seleccionar al menos un empleado',
            'carga_empleados.min' => 'Debe seleccionar al menos un empleado',
        ]);
        
        $this->cargas[] = [
            'id_categoria_madera' => $this->carga_id_categoria_madera,
            'ticket' => $this->carga_ticket,
            'peso_bruto' => $this->carga_peso_bruto,
            'tara' => $this->carga_tara,
            'peso_neto' => $this->carga_peso_neto,
            'id_chofer' => $this->carga_id_chofer,
            'destino' => $this->carga_destino, // ID del cliente
            'empleados' => $this->carga_empleados,
        ];
        
        $this->calcularTotalToneladas();
        $this->resetCargaForm();
    }
    
    public function eliminarCarga($index)
    {
        unset($this->cargas[$index]);
        $this->cargas = array_values($this->cargas);
        $this->calcularTotalToneladas();
    }
    
    private function calcularTotalToneladas()
    {
        $this->total_toneladas = array_sum(array_column($this->cargas, 'peso_neto'));
    }
    
    private function resetCargaForm()
    {
        $this->carga_id_categoria_madera = null;
        $this->carga_ticket = null;
        $this->carga_peso_bruto = null;
        $this->carga_tara = null;
        $this->carga_peso_neto = null;
        $this->carga_id_chofer = null;
        $this->carga_destino = null;
        $this->carga_empleados = [];
        $this->busqueda_chofer = '';
        $this->busqueda_cliente = '';
    }
    
    // ============ GESTIÓN DE JORNALES (DÍA CAÍDO) ============
    
    public function agregarJornal()
    {
        $this->validate([
            'jornal_id_empleado' => 'required|exists:empleados,id_empleado',
        ], [
            'jornal_id_empleado.required' => 'Debe seleccionar un empleado',
        ]);
        
        // Verificar que no esté ya agregado
        foreach ($this->jornales as $j) {
            if ($j['id_empleado'] == $this->jornal_id_empleado) {
                session()->flash('error', 'El empleado ya está en la lista.');
                return;
            }
        }
        
        $empleado = Empleado::with('rolLaboral')->find($this->jornal_id_empleado);
        
        $this->jornales[] = [
            'id_empleado' => $empleado->id_empleado,
            'nombre_completo' => $empleado->apellido . ', ' . $empleado->nombre,
            'rol' => $empleado->rolLaboral->nombre ?? 'N/A',
            'jornal_diario' => $empleado->rolLaboral->jornal_diario ?? 0,
            'observaciones' => $this->jornal_observaciones,
        ];
        
        $this->resetJornalForm();
    }
    
    public function eliminarJornal($index)
    {
        unset($this->jornales[$index]);
        $this->jornales = array_values($this->jornales);
    }
    
    private function resetJornalForm()
    {
        $this->jornal_id_empleado = null;
        $this->jornal_observaciones = null;
    }
    
    // ============ GESTIÓN DE MOVIMIENTOS DE INSUMOS ============
    
    public function agregarMovimiento()
    {
        $this->validate([
            'movimiento_id_insumo' => 'required|exists:insumos,id_insumo',
            'movimiento_tipo' => 'required|in:entrada,salida',
            'movimiento_cantidad' => 'required|numeric|min:0.01',
            'movimiento_motivo' => 'required|in:Producción,Mantenimiento,Varios',
        ], [
            'movimiento_id_insumo.required' => 'Debe seleccionar un insumo',
            'movimiento_tipo.required' => 'El tipo de movimiento es obligatorio',
            'movimiento_cantidad.required' => 'La cantidad es obligatoria',
            'movimiento_cantidad.min' => 'La cantidad debe ser mayor a 0',
            'movimiento_motivo.required' => 'El motivo es obligatorio',
        ]);
        
        $insumo = Insumo::with('unidadMedida')->find($this->movimiento_id_insumo);
        
        $this->movimientos[] = [
            'id_insumo' => $insumo->id_insumo,
            'nombre_insumo' => $insumo->nombre,
            'tipo' => $this->movimiento_tipo,
            'cantidad' => $this->movimiento_cantidad,
            'motivo' => $this->movimiento_motivo,
            'observaciones' => $this->movimiento_observaciones,
            'unidad' => $insumo->unidadMedida->nombre ?? 'Unidad',
        ];
        
        $this->resetMovimientoForm();
    }
    
    public function eliminarMovimiento($index)
    {
        unset($this->movimientos[$index]);
        $this->movimientos = array_values($this->movimientos);
    }
    
    private function resetMovimientoForm()
    {
        $this->movimiento_id_insumo = null;
        $this->movimiento_tipo = 'entrada';
        $this->movimiento_cantidad = null;
        $this->movimiento_motivo = 'Producción';
        $this->movimiento_observaciones = null;
    }

    public function guardar()
    {
        $this->validate();
        
        // Validaciones adicionales
        if (!$this->es_dia_caido && empty($this->cargas)) {
            session()->flash('error', 'Debe registrar al menos una carga para modo producción.');
            return;
        }
        
        if ($this->es_dia_caido && empty($this->jornales)) {
            session()->flash('error', 'Debe agregar al menos un empleado para el jornal por día caído.');
            return;
        }

        try {
            \DB::beginTransaction();
            
            // 1. Guardar el Parte Diario (Maestro)
            $parteDiario = ParteDiario::updateOrCreate(
                ['id_parte_diario' => $this->parte_id],
                [
                    'id_lote' => $this->id_lote,
                    'fecha' => $this->fecha,
                    'es_dia_caido' => $this->es_dia_caido,
                    'observaciones' => $this->observaciones,
                    'activo' => true,
                ]
            );
            
            $parteDiarioId = $parteDiario->id_parte_diario;
            
            // 2. Guardar Detalles según el tipo de día
            if (!$this->es_dia_caido) {
                // MODO PRODUCCIÓN: Guardar Cargas con empleados por destajo
                foreach ($this->cargas as $cargaData) {
                    $carga = Carga::create([
                        'id_parte_diario' => $parteDiarioId,
                        'id_lote' => $this->id_lote,
                        'id_categoria_madera' => $cargaData['id_categoria_madera'],
                        'id_chofer' => $cargaData['id_chofer'],
                        'ticket' => $cargaData['ticket'],
                        'peso_bruto' => $cargaData['peso_bruto'],
                        'tara' => $cargaData['tara'],
                        'peso_neto' => $cargaData['peso_neto'],
                        'destino' => $cargaData['destino'], // ID del cliente
                        'fecha_carga' => $this->fecha,
                    ]);
                    
                    // Guardar empleados asignados a esta carga (para pago por tonelada)
                    // Usaremos la tabla 'recibos' para registrar el pago por destajo
                    foreach ($cargaData['empleados'] as $empleadoId) {
                        $empleado = Empleado::with('rolLaboral')->find($empleadoId);
                        
                        if ($empleado && $empleado->rolLaboral) {
                            // Calcular pago por tonelada: (peso_neto / cantidad_empleados) * precio_por_tonelada
                            $cantidadEmpleados = count($cargaData['empleados']);
                            $toneladasPorEmpleado = $cargaData['peso_neto'] / $cantidadEmpleados;
                            $precioPorTonelada = $empleado->rolLaboral->precio_tonelada ?? 0;
                            $monto = $toneladasPorEmpleado * $precioPorTonelada;
                            
                            // Crear un recibo por el pago de destajo
                            \App\Models\Recibo::create([
                                'id_empleado' => $empleadoId,
                                'fecha_emision' => $this->fecha,
                                'monto_bruto' => $monto,
                                'descuentos' => 0,
                                'monto' => $monto,
                                'observaciones' => 'Pago por destajo - Parte Diario #' . $parteDiarioId . ' - Carga #' . $carga->id_carga . ' (' . number_format($toneladasPorEmpleado, 2) . ' ton)',
                                'activo' => true,
                            ]);
                        }
                    }
                }
            } else {
                // MODO DÍA CAÍDO: Guardar Jornales (pago fijo por día)
                foreach ($this->jornales as $jornalData) {
                    // Crear un recibo por el pago de jornal
                    $montoBruto = $jornalData['jornal_diario'];
                    \App\Models\Recibo::create([
                        'id_empleado' => $jornalData['id_empleado'],
                        'fecha_emision' => $this->fecha,
                        'monto_bruto' => $montoBruto,
                        'descuentos' => 0,
                        'monto' => $montoBruto,
                        'observaciones' => 'Pago por jornal - Día Caído - Parte Diario #' . $parteDiarioId . ' - ' . ($this->motivo_dia_caido ? 'Motivo: ' . $this->motivo_dia_caido : '') . ($jornalData['observaciones'] ? ' - ' . $jornalData['observaciones'] : ''),
                        'activo' => true,
                    ]);
                }
            }
            
            // 3. Guardar Movimientos de Insumos (siempre se guardan)
            foreach ($this->movimientos as $movData) {
                MovimientoStock::create([
                    'id_insumo' => $movData['id_insumo'],
                    'tipo' => $movData['tipo'],
                    'cantidad' => $movData['cantidad'],
                    'fecha' => $this->fecha,
                    'motivo' => 'Parte Diario #' . $parteDiarioId . ' - ' . $movData['motivo'] . ($movData['observaciones'] ? ' - ' . $movData['observaciones'] : ''),
                ]);
                
                // Actualizar stock del insumo
                $insumo = Insumo::find($movData['id_insumo']);
                if ($insumo) {
                    if ($movData['tipo'] == 'entrada') {
                        $insumo->stock += $movData['cantidad'];
                    } else {
                        $insumo->stock -= $movData['cantidad'];
                    }
                    $insumo->save();
                }
            }
            
            \DB::commit();
            
            $this->cargarPartes();
            session()->flash('message', $this->parte_id ? 'Parte diario actualizado correctamente con todos sus detalles.' : 'Parte diario creado correctamente con todos sus detalles.');
            $this->resetCampos();
            $this->dispatch('parteDiarioGuardado');
            
        } catch (\Exception $e) {
            \DB::rollBack();
            session()->flash('error', 'Error al guardar el parte diario: ' . $e->getMessage());
            \Log::error('Error en PartesDiarios::guardar()', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    public function editar($id)
    {
        $parte = ParteDiario::findOrFail($id);
        $this->parte_id = $parte->id_parte_diario;
        $this->id_lote = $parte->id_lote;
        $this->fecha = $parte->fecha;
        $this->es_dia_caido = (bool) $parte->es_dia_caido;
        $this->observaciones = $parte->observaciones;
        
        // TODO: Cargar detalles asociados (cargas, jornales, movimientos)
    }

    public function eliminar($id)
    {
        ParteDiario::findOrFail($id)->delete();
        $this->cargarPartes();
        session()->flash('message', 'Parte diario eliminado correctamente.');
    }

    public function resetCampos()
    {
        $this->reset([
            'parte_id', 'id_lote', 'fecha', 'actividad_realizada', 'es_dia_caido', 
            'motivo_dia_caido', 'observaciones', 'cargas', 'jornales', 'movimientos',
            'carga_peso_neto', 'carga_id_chofer', 'carga_patente', 'carga_destino', 'carga_empleados',
            'jornal_id_empleado', 'jornal_observaciones',
            'movimiento_id_insumo', 'movimiento_tipo', 'movimiento_cantidad'
        ]);
        $this->total_toneladas = 0;
    }
}
