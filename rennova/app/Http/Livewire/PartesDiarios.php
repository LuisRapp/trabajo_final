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
    public $empleados_asignados_ids = [];
    public $maquinarias = [];
    public $maquinarias_asignadas_ids = [];
    // selección por carga (múltiple)
    public $carga_maquinarias = [];
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
    public $jornal_por_empleado = [];
    
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
        $this->maquinarias = \App\Models\Maquinaria::with('tipoMaquinaria')->orderBy('modelo')->get();
        $this->choferes = Chofer::where('estado', true)->orderBy('apellido')->get();
        $this->insumos = Insumo::orderBy('nombre')->get();
        $this->categorias_madera = CategoriaMadera::orderBy('nombre')->get();
        $this->clientes = Cliente::orderBy('razon_social')->get();
        $this->cargarPartes();
        $this->actualizarJornalPorEmpleado();
    }

    public function updatedIdLote()
    {
        // Al cambiar el lote, cargar empleados y maquinarias asignadas para filtrar
        $this->empleados_asignados_ids = [];
    $this->maquinarias_asignadas_ids = [];
    $this->carga_maquinarias = [];
        
        if ($this->id_lote) {
            $lote = Lote::with(['empleados:id_empleado', 'maquinarias:id_maquinaria'])->find($this->id_lote);
            if ($lote) {
                $this->empleados_asignados_ids = $lote->empleados->pluck('id_empleado')->toArray();
                $this->maquinarias_asignadas_ids = $lote->maquinarias->pluck('id_maquinaria')->toArray();
                
                // Preselección automática si hay solo una maquinaria asignada (selección múltiple)
                if (count($this->maquinarias_asignadas_ids) === 1) {
                    $this->carga_maquinarias = [$this->maquinarias_asignadas_ids[0]];
                }
            }
        }
    }

    public function getEmpleadosFiltradosProperty()
    {
        if (empty($this->empleados_asignados_ids)) {
            return $this->empleados;
        }
        return $this->empleados->filter(function($emp) {
            return in_array($emp->id_empleado, $this->empleados_asignados_ids);
        });
    }

    public function getMaquinariasFiltradaProperty()
    {
        if (empty($this->maquinarias_asignadas_ids)) {
            return $this->maquinarias;
        }
        return $this->maquinarias->filter(function($maq) {
            return in_array($maq->id_maquinaria, $this->maquinarias_asignadas_ids);
        });
    }

    public function updatedFecha()
    {
        // Cuando cambia la fecha del parte, recalcular jornal vigente por empleado
        $this->actualizarJornalPorEmpleado();
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
            'carga_maquinarias' => 'nullable|array',
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
            // no forzamos maquinaria obligatoria; se permite vacío
        ]);
        
        // Obtener el nombre del cliente para mostrar en la tabla
        $cliente = Cliente::find($this->carga_destino);
        $nombreCliente = $cliente ? $cliente->razon_social : 'Cliente no encontrado';
        
        $this->cargas[] = [
            'id_categoria_madera' => $this->carga_id_categoria_madera,
            'ticket' => $this->carga_ticket,
            'peso_bruto' => $this->carga_peso_bruto,
            'tara' => $this->carga_tara,
            'peso_neto' => $this->carga_peso_neto,
            'id_chofer' => $this->carga_id_chofer,
            'destino' => $this->carga_destino, // ID del cliente (se convertirá al guardar en BD)
            'destino_nombre' => $nombreCliente, // Nombre para mostrar en la tabla
            'empleados' => $this->carga_empleados,
            'maquinarias' => $this->carga_maquinarias,
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
        $this->carga_maquinarias = [];
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
    // Obtener jornal vigente para la fecha del parte
    $jornalVigente = $this->obtenerJornalEmpleadoParaFecha($empleado?->id_empleado, $this->fecha) ?? 0;
        
        $this->jornales[] = [
            'id_empleado' => $empleado->id_empleado,
            'nombre_completo' => $empleado->apellido . ', ' . $empleado->nombre,
            'rol' => $empleado->rolLaboral->nombre ?? 'N/A',
            'jornal_diario' => $jornalVigente,
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

    private function actualizarJornalPorEmpleado()
    {
        $this->jornal_por_empleado = [];
        if (!$this->fecha) {
            return;
        }
        foreach ($this->empleados as $emp) {
            $this->jornal_por_empleado[$emp->id_empleado] = $this->obtenerJornalEmpleadoParaFecha($emp->id_empleado, $this->fecha) ?? (float)($emp->rolLaboral->jornal_diario ?? 0);
        }
    }

    private function obtenerJornalEmpleadoParaFecha($empleadoId, $fecha)
    {
        if (!$empleadoId || !$fecha) {
            return null;
        }
        $empleado = $this->empleados->firstWhere('id_empleado', $empleadoId);
        if (!$empleado || !$empleado->rolLaboral) {
            return null;
        }
        $rolId = $empleado->rolLaboral->id_rol_laboral ?? $empleado->id_rol_laboral ?? null;
        if (!$rolId) {
            return null;
        }
        $hist = \App\Models\HistoricoRolLaboral::where('rol_laboral_id', $rolId)
            ->whereDate('fecha_inicio', '<=', $fecha)
            ->where(function($q) use ($fecha) {
                $q->whereNull('fecha_fin')->orWhereDate('fecha_fin', '>=', $fecha);
            })
            ->orderBy('fecha_inicio', 'desc')
            ->first();
        if ($hist) {
            return (float) ($hist->jornal_diario ?? 0);
        }
        // Fallback al valor actual del rol si no hay histórico
        return (float) ($empleado->rolLaboral->jornal_diario ?? 0);
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
                // Si estamos en modo edición, eliminar cargas anteriores del parte para evitar duplicados
                if ($this->parte_id) {
                    $cargasAnteriores = Carga::where('id_parte_diario', $parteDiarioId)->get();
                    foreach ($cargasAnteriores as $cAnterior) {
                        $cAnterior->delete(); // cascada elimina pivote carga_empleado
                    }
                }
                // MODO PRODUCCIÓN: Guardar Cargas con empleados por destajo
                foreach ($this->cargas as $cargaData) {
                    // Obtener el nombre del cliente a partir del ID
                    $cliente = Cliente::find($cargaData['destino']);
                    $nombreDestino = $cliente ? $cliente->razon_social : 'Cliente no encontrado';
                    
                    $carga = Carga::create([
                        'id_parte_diario' => $parteDiarioId,
                        'id_lote' => $this->id_lote,
                        'id_categoria_madera' => $cargaData['id_categoria_madera'],
                        'id_chofer' => $cargaData['id_chofer'],
                        'ticket' => $cargaData['ticket'],
                        'peso_bruto' => $cargaData['peso_bruto'],
                        'tara' => $cargaData['tara'],
                        'peso_neto' => $cargaData['peso_neto'],
                        'destino' => $nombreDestino, // Nombre del cliente
                        'fecha_carga' => $this->fecha,
                    ]);
                    
                    // Guardar empleados asignados a esta carga (para cálculo de pago posterior)
                    // Ya NO creamos recibos aquí - los recibos se crean manualmente usando calcularPagoRango()
                    $carga->empleados()->sync($cargaData['empleados']);
                    // Guardar maquinarias utilizadas en esta carga (múltiples)
                    $carga->maquinarias()->sync($cargaData['maquinarias'] ?? []);
                }
            } else {
                // MODO DÍA CAÍDO: Guardar empleados que trabajaron ese día
                // Ya NO creamos recibos automáticamente - se crearán después con calcularPagoRango()
                $empleadosIds = array_column($this->jornales, 'id_empleado');
                $parteDiario->empleados()->sync($empleadosIds);
            }
            
            // 3. Guardar Movimientos de Insumos (siempre se guardan)
            // Si estamos en edición, eliminar movimientos previos de este parte para no duplicar
            if ($this->parte_id) {
                MovimientoStock::whereDate('fecha', $this->fecha)
                    ->where('motivo', 'ILIKE', 'Parte Diario #' . $parteDiarioId . ' - %')
                    ->delete();
            }
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
        $parte = ParteDiario::with(['empleados.rolLaboral'])->findOrFail($id);
        $this->parte_id = $parte->id_parte_diario;
        $this->id_lote = $parte->id_lote;
        $this->fecha = $parte->fecha;
        $this->es_dia_caido = (bool) $parte->es_dia_caido;
        $this->observaciones = $parte->observaciones;

        // Cargar CARGAS si es producción
        $this->cargas = [];
        if (!$this->es_dia_caido) {
            $cargas = Carga::with(['empleados','maquinarias'])
                ->where('id_parte_diario', $parte->id_parte_diario)
                ->get();
            foreach ($cargas as $c) {
                // Buscar el ID del cliente a partir del nombre guardado en destino
                $cliente = Cliente::where('razon_social', $c->destino)->first();
                $idCliente = $cliente ? $cliente->id_cliente : null;
                
                $this->cargas[] = [
                    'id_categoria_madera' => $c->id_categoria_madera,
                    'ticket' => $c->ticket,
                    'peso_bruto' => (float) $c->peso_bruto,
                    'tara' => (float) $c->tara,
                    'peso_neto' => (float) $c->peso_neto,
                    'id_chofer' => $c->id_chofer,
                    'destino' => $idCliente, // ID del cliente para el select
                    'destino_nombre' => $c->destino, // Nombre original
                    'empleados' => $c->empleados->pluck('id_empleado')->all(),
                    'maquinarias' => $c->maquinarias->pluck('id_maquinaria')->all(),
                ];
            }
            $this->calcularTotalToneladas();
        }

        // Cargar JORNALES si es día caído
        $this->jornales = [];
        if ($this->es_dia_caido) {
            foreach ($parte->empleados as $emp) {
                $jornalVig = $this->obtenerJornalEmpleadoParaFecha($emp->id_empleado, $this->fecha) ?? 0;
                $this->jornales[] = [
                    'id_empleado' => $emp->id_empleado,
                    'nombre_completo' => $emp->apellido . ', ' . $emp->nombre,
                    'rol' => $emp->rolLaboral->nombre ?? 'N/A',
                    'jornal_diario' => $jornalVig,
                    'observaciones' => null,
                ];
            }
        }

        // Cargar MOVIMIENTOS vinculados a este parte (por motivo y fecha)
        $this->movimientos = [];
        $movs = MovimientoStock::whereDate('fecha', $this->fecha)
            ->where('motivo', 'ILIKE', 'Parte Diario #' . $parte->id_parte_diario . ' - %')
            ->get();
        foreach ($movs as $m) {
            // Parsear motivo para extraer el enum original y observaciones
            $motivoTexto = $m->motivo; // Ej: "Parte Diario #ID - Producción - obs"
            $sinPrefijo = preg_replace('/^Parte Diario #'.preg_quote($parte->id_parte_diario, '/').' - /', '', $motivoTexto);
            $partesMotivo = explode(' - ', $sinPrefijo, 2);
            $motivoEnum = $partesMotivo[0] ?? 'Producción';
            $obs = $partesMotivo[1] ?? null;

            $insumo = $this->insumos->firstWhere('id_insumo', $m->id_insumo);
            $this->movimientos[] = [
                'id_insumo' => $m->id_insumo,
                'nombre_insumo' => $insumo->nombre ?? 'Insumo',
                'tipo' => $m->tipo,
                'cantidad' => (float) $m->cantidad,
                'motivo' => $motivoEnum,
                'observaciones' => $obs,
                'unidad' => $insumo->unidadMedida->nombre ?? 'Unidad',
            ];
        }

        // Asegurar que el mapa de jornales vigentes esté actualizado
        $this->actualizarJornalPorEmpleado();
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
            'carga_peso_neto', 'carga_id_chofer', 'carga_destino', 'carga_empleados',
            'jornal_id_empleado', 'jornal_observaciones',
            'movimiento_id_insumo', 'movimiento_tipo', 'movimiento_cantidad'
        ]);
        $this->total_toneladas = 0;
        $this->actualizarJornalPorEmpleado();
    }
}
