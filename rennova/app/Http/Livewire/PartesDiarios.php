<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Events\CargaRegistrada;
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
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PartesDiarios extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    // Parte Diario Principal
    // Listado paginado se entrega desde render(), no como estado
    public $parte_id;
    public $id_lote;
    public $fecha;
    public $actividad_realizada;
    public $es_dia_caido = false;
    public $motivo_dia_caido;
    public $observaciones;
    public $busqueda = '';
    public $busqueda_fecha = '';
    
    // Catálogos (lazy loaded via computed properties)
    protected $lotesCache;
    protected $empleadosFiltradosCache;
    protected $maquinariasFiltradaCache;
    // Catálogos pesados se obtienen vía propiedades computadas para evitar deshidratación
    public $empleados_asignados_ids = [];
    
    // Se usa propiedad computada
    public $maquinarias_asignadas_ids = [];
    // selección por carga (múltiple)
    public $carga_maquinarias = [];
    // Catálogos: se resolverán por getters computados
    
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
    public $movimiento_cantidad;
    public $movimiento_motivo = 'Producción';
    public $movimiento_observaciones;
    public $stock_disponible_insumo = null; // Para mostrar en UI

    protected function rules()
    {
        $fechaMinima = Carbon::today()->subDays(7)->toDateString();
        
        $rules = [
            'id_lote' => 'required|exists:lotes,id_lote',
            // Validar fecha: solo hoy o dentro de los últimos 7 días
            'fecha' => [
                'required',
                'date',
                'before_or_equal:today',
                'after_or_equal:' . $fechaMinima,
            ],
            'observaciones' => 'nullable|string',
        ];
        
        return $rules;
    }

    protected function messages()
    {
        $fechaMinima = Carbon::today()->subDays(7)->format('d/m/Y');
        
        return [
            'fecha.required' => 'La fecha es obligatoria.',
            'fecha.date' => 'La fecha no es válida.',
            'fecha.before_or_equal' => 'No se pueden crear partes diarios para fechas futuras. Solo hoy o días anteriores.',
            'fecha.after_or_equal' => "No se pueden crear partes diarios con más de 7 días de antigüedad. Fecha mínima permitida: {$fechaMinima}.",
            'id_lote.required' => 'Debe seleccionar un lote.',
        ];
    }

    public function mount()
    {
        // Los lotes se cargan lazy via propiedad computada
        // actualizarJornalPorEmpleado se llama cuando hay fecha
    }

    public function updatedIdLote()
    {
        // Al cambiar el lote, cargar empleados y maquinarias asignadas para filtrar
        $this->empleados_asignados_ids = [];
        $this->maquinarias_asignadas_ids = [];
        $this->carga_maquinarias = [];
        $this->carga_empleados = [];
        
        if ($this->id_lote) {
            // Query directa sin validación extra - más rápido
            $this->empleados_asignados_ids = \DB::table('lote_empleado')
                ->where('id_lote', $this->id_lote)
                ->pluck('id_empleado')
                ->toArray();
                
            $this->maquinarias_asignadas_ids = \DB::table('lote_maquinaria')
                ->where('id_lote', $this->id_lote)
                ->pluck('id_maquinaria')
                ->toArray();
        }
        
        // Limpiar cache de propiedades computadas
        unset($this->empleadosFiltradosCache, $this->maquinariasFiltradaCache);
    }

    public function getEmpleadosFiltradosProperty()
    {
        if (isset($this->empleadosFiltradosCache)) {
            return $this->empleadosFiltradosCache;
        }
        
        $empleados = $this->empleados; // propiedad computada
        if (empty($this->empleados_asignados_ids)) {
            $this->empleadosFiltradosCache = $empleados;
        } else {
            $this->empleadosFiltradosCache = $empleados->filter(function($emp) {
                return in_array($emp->id_empleado, $this->empleados_asignados_ids);
            });
        }
        return $this->empleadosFiltradosCache;
    }

    public function getMaquinariasFiltradaProperty()
    {
        if (isset($this->maquinariasFiltradaCache)) {
            return $this->maquinariasFiltradaCache;
        }
        
        $maquinarias = $this->maquinarias; // propiedad computada
        if (empty($this->maquinarias_asignadas_ids)) {
            $this->maquinariasFiltradaCache = $maquinarias;
        } else {
            $this->maquinariasFiltradaCache = $maquinarias->filter(function($maq) {
                return in_array($maq->id_maquinaria, $this->maquinarias_asignadas_ids);
            });
        }
        return $this->maquinariasFiltradaCache;
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
    
    public function updatedCargaPesoNeto()
    {
        // No hacer nada; es calculado pero el usuario puede overridear
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
            return $this->choferes; // computada
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
            return $this->clientes; // computada
        }
        
        $busq = strtolower($this->busqueda_cliente);
        return $this->clientes->filter(function($cliente) use ($busq) {
            return str_contains(strtolower($cliente->razon_social), $busq);
        });
    }

    public function render()
    {
        $query = ParteDiario::with('lote')->orderBy('fecha', 'desc')->orderBy('id_parte_diario', 'desc');

        // Buscar por propietario del lote
        if ($this->busqueda) {
            $busq = $this->busqueda;
            $query->whereHas('lote', function($ql) use ($busq) {
                $ql->where('propietario', 'ILIKE', '%' . $busq . '%');
            });
        }
        
        // Buscar por fecha exacta
        if ($this->busqueda_fecha) {
            $query->whereDate('fecha', $this->busqueda_fecha);
        }

        $partes = $query->paginate(10);
        return view('livewire.partes-diarios', compact('partes'));
    }

    // Método eliminado: render() ya maneja la paginación

    public function updatedBusqueda()
    {
        $this->resetPage();
    }
    
    public function updatedBusquedaFecha()
    {
        $this->resetPage();
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
            'carga_maquinarias' => 'required|array|min:1',
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
            'carga_maquinarias.required' => 'Debe seleccionar al menos una maquinaria para la carga',
            'carga_maquinarias.min' => 'Debe seleccionar al menos una maquinaria para la carga',
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
        \Log::info('agregarMovimiento llamado', [
            'id_insumo' => $this->movimiento_id_insumo,
            'cantidad' => $this->movimiento_cantidad,
            'motivo' => $this->movimiento_motivo
        ]);
        
        try {
            $this->validate([
                'movimiento_id_insumo' => 'required|exists:insumos,id_insumo',
                'movimiento_cantidad' => 'required|numeric|min:0.01',
                'movimiento_motivo' => 'required|in:Producción,Mantenimiento,Varios',
            ], [
                'movimiento_id_insumo.required' => 'Debe seleccionar un insumo',
                'movimiento_cantidad.required' => 'La cantidad es obligatoria',
                'movimiento_cantidad.min' => 'La cantidad debe ser mayor a 0',
                'movimiento_motivo.required' => 'El motivo es obligatorio',
            ]);
            
            \Log::info('Validación pasada');
            
            // Validar stock disponible
            $stockDisponible = MovimientoStock::stockDisponible($this->movimiento_id_insumo);
            \Log::info('Stock disponible', ['stock' => $stockDisponible, 'requerido' => $this->movimiento_cantidad]);
            
            if ($this->movimiento_cantidad > $stockDisponible) {
                \Log::warning('Stock insuficiente');
                $this->dispatch('mostrarError', mensaje: "Stock insuficiente. Disponible: {$stockDisponible}");
                return;
            }
            
            $insumo = Insumo::with('unidadMedida')->find($this->movimiento_id_insumo);
            
            $this->movimientos[] = [
                'id_insumo' => $insumo->id_insumo,
                'nombre_insumo' => $insumo->nombre,
                'tipo' => 'salida', // Siempre salida (consumo)
                'cantidad' => $this->movimiento_cantidad,
                'motivo' => $this->movimiento_motivo,
                'observaciones' => $this->movimiento_observaciones,
                'unidad' => $insumo->unidadMedida->nombre ?? 'Unidad',
            ];
            
            \Log::info('Insumo agregado exitosamente', ['total_movimientos' => count($this->movimientos)]);
            $this->dispatch('mostrarExito', mensaje: 'Insumo agregado correctamente');
            $this->resetMovimientoForm();
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Error de validación: ' . json_encode($e->errors()));
            throw $e;
        } catch (\Exception $e) {
            \Log::error('Error en agregarMovimiento: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            $this->dispatch('mostrarError', mensaje: 'Error al agregar insumo: ' . $e->getMessage());
        }
    }
    
    public function eliminarMovimiento($index)
    {
        unset($this->movimientos[$index]);
        $this->movimientos = array_values($this->movimientos);
    }
    
    private function resetMovimientoForm()
    {
        $this->movimiento_id_insumo = null;
        $this->movimiento_cantidad = null;
        $this->movimiento_motivo = 'Producción';
        $this->movimiento_observaciones = null;
        $this->stock_disponible_insumo = null;
    }
    
    public function updatedMovimientoIdInsumo($value)
    {
        if ($value) {
            $this->stock_disponible_insumo = MovimientoStock::stockDisponible($value);
        } else {
            $this->stock_disponible_insumo = null;
        }
    }

    public function guardar()
    {
        $this->validate();
        
        // Guard adicional: bloquear fecha futura por seguridad
        if (Carbon::parse($this->fecha)->isAfter(Carbon::today())) {
            session()->flash('error', 'La fecha del parte no puede ser futura.');
            return;
        }
        
        // Guard adicional: bloquear fecha muy antigua
        if (Carbon::parse($this->fecha)->isBefore(Carbon::today()->subDays(7))) {
            $fechaMinima = Carbon::today()->subDays(7)->format('d/m/Y');
            session()->flash('error', "No se pueden crear partes con más de 7 días de antigüedad. Fecha mínima: {$fechaMinima}.");
            return;
        }
        
        // Validaciones adicionales
        if (!$this->es_dia_caido && empty($this->cargas)) {
            session()->flash('error', 'Debe registrar al menos una carga para modo producción.');
            return;
        }
        // Validar que cada carga tenga al menos una maquinaria asociada
        if (!$this->es_dia_caido) {
            foreach ($this->cargas as $idx => $c) {
                $maqs = $c['maquinarias'] ?? [];
                if (empty($maqs)) {
                    session()->flash('error', 'La carga #' . ($idx + 1) . ' no tiene maquinarias seleccionadas. Asigne al menos una maquinaria.');
                    return;
                }
            }
        }
        
        if ($this->es_dia_caido && empty($this->jornales)) {
            session()->flash('error', 'Debe agregar al menos un empleado para el jornal por día caído.');
            return;
        }

        try {
            \DB::beginTransaction();
            $eventosCarga = [];
            
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

                    // Programar evento para actualizar odómetro de las maquinarias (post-commit)
                    $maqs = $cargaData['maquinarias'] ?? [];
                    if (!empty($maqs)) {
                        // Normalizar unidades: si el usuario ingresó en kg o en toneladas
                        $valorIngresado = (float) ($cargaData['peso_neto'] ?? 0);
                        // Heurística: si es mayor a 1000 asumimos kg; si no, asumimos toneladas
                        $toneladasTotales = $valorIngresado > 1000 ? ($valorIngresado / 1000.0) : $valorIngresado;
                        $porMaquinaria = count($maqs) > 0 ? $toneladasTotales / count($maqs) : 0;

                        foreach ($maqs as $maqId) {
                            $eventosCarga[] = [$carga, $maqId, $porMaquinaria];
                        }

                        \Log::info('Eventos CargaRegistrada acumulados (post-commit) desde ParteDiario', [
                            'parte_diario_id' => $parteDiarioId,
                            'carga_id' => $carga->id_carga,
                            'maquinarias' => $maqs,
                            'valor_ingresado' => $valorIngresado,
                            'toneladas_totales' => $toneladasTotales,
                            'toneladas_por_maquinaria' => $porMaquinaria,
                        ]);
                    }
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
            
            // Registrar movimientos con cálculo FIFO automático para salidas
            foreach ($this->movimientos as $movData) {
                $motivo = 'Parte Diario #' . $parteDiarioId . ' - ' . $movData['motivo'] . 
                         ($movData['observaciones'] ? ' - ' . $movData['observaciones'] : '');
                
                if ($movData['tipo'] == 'salida') {
                    // Usar FIFO para salidas (consumos de producción)
                    try {
                        $resultado = MovimientoStock::registrarSalida(
                            $movData['id_insumo'],
                            $movData['cantidad'],
                            $motivo,
                            $this->fecha
                        );
                        
                        // Opcional: guardar detalle del costo FIFO en log para auditoría
                        \Log::info('Consumo FIFO en Parte Diario', [
                            'parte_diario_id' => $parteDiarioId,
                            'insumo_id' => $movData['id_insumo'],
                            'cantidad' => $movData['cantidad'],
                            'costo_total' => $resultado['costo_total'],
                            'lotes_consumidos' => count($resultado['lotes_consumidos'])
                        ]);
                        
                    } catch (\Exception $e) {
                        // Si hay stock insuficiente, lanzar excepción para rollback
                        throw new \Exception("Stock insuficiente para insumo ID {$movData['id_insumo']}: " . $e->getMessage());
                    }
                    
                } else {
                    // Este caso ya no debería ocurrir, pero por seguridad:
                    throw new \Exception("Solo se permiten salidas de insumos en Parte Diario. Use Gestión de Stock para entradas.");
                }
            }

            
            \DB::commit();

            // Despachar eventos después del commit para garantizar consistencia
            foreach ($eventosCarga as [$carga, $maqId, $ton]) {
                event(new CargaRegistrada($carga, $maqId, $ton));
            }
            
            // Calcular y guardar costos del parte diario
            try {
                $parteDiario->calcularYGuardarCostos();
            } catch (\Exception $e) {
                \Log::error('Error al calcular costos del parte diario', [
                    'parte_id' => $parteDiario->id_parte_diario,
                    'error' => $e->getMessage()
                ]);
                // No lanzar excepción para no bloquear el guardado del parte
            }
            
            // $this->cargarPartes(); // Método eliminado por no existir
            $mensaje = $this->parte_id ? 'Parte diario actualizado correctamente con todos sus detalles.' : 'Parte diario creado correctamente con todos sus detalles.';
            $this->resetCampos();
            session()->flash('message', $mensaje);
            // No despachar evento para evitar cambio de pestaña automático
            // $this->dispatch('parteDiarioGuardado');
            
        } catch (\Exception $e) {
            \DB::rollBack();
            session()->flash('error', 'Error al guardar el parte diario: ' . $e->getMessage());
            \Log::error('Error en PartesDiarios::guardar()', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return; // Evita que la página quede en blanco
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
        
        // Agrupar múltiples movimientos FIFO del mismo insumo en uno solo para edición
        $movimientosAgrupados = [];
        foreach ($movs as $m) {
            // Parsear motivo para extraer el enum original y observaciones
            $motivoTexto = $m->motivo; // Ej: "Parte Diario #ID - Producción - obs"
            $sinPrefijo = preg_replace('/^Parte Diario #'.preg_quote($parte->id_parte_diario, '/').' - /', '', $motivoTexto);
            $partesMotivo = explode(' - ', $sinPrefijo, 2);
            $motivoEnum = $partesMotivo[0] ?? 'Producción';
            $obs = $partesMotivo[1] ?? null;

            $insumo = $this->insumos->firstWhere('id_insumo', $m->id_insumo);
            
            // Clave única por insumo+tipo+motivo
            $clave = $m->id_insumo . '_' . $m->tipo . '_' . $motivoEnum;
            
            if (!isset($movimientosAgrupados[$clave])) {
                $movimientosAgrupados[$clave] = [
                    'id_insumo' => $m->id_insumo,
                    'nombre_insumo' => $insumo->nombre ?? 'Insumo',
                    'tipo' => $m->tipo,
                    'cantidad' => 0,
                    'motivo' => $motivoEnum,
                    'observaciones' => $obs,
                    'unidad' => $insumo->unidadMedida->nombre ?? 'Unidad',
                ];
            }
            
            // Acumular cantidad (para movimientos FIFO múltiples del mismo insumo)
            $movimientosAgrupados[$clave]['cantidad'] += (float) $m->cantidad;
        }
        
        $this->movimientos = array_values($movimientosAgrupados);

        // Asegurar que el mapa de jornales vigentes esté actualizado
        $this->actualizarJornalPorEmpleado();
    }

    public function eliminar($id)
    {
        ParteDiario::findOrFail($id)->delete();
        // $this->cargarPartes();
        session()->flash('message', 'Parte diario eliminado correctamente.');
    }

    public function resetCampos()
    {
        $this->reset([
            'parte_id', 'id_lote', 'fecha', 'actividad_realizada', 'es_dia_caido', 
            'motivo_dia_caido', 'observaciones', 'cargas', 'jornales', 'movimientos',
            'carga_peso_neto', 'carga_id_chofer', 'carga_destino', 'carga_empleados',
            'jornal_id_empleado', 'jornal_observaciones',
            'movimiento_id_insumo', 'movimiento_cantidad', 'movimiento_motivo', 'movimiento_observaciones'
        ]);
        $this->total_toneladas = 0;
        $this->stock_disponible_insumo = null;
        $this->actualizarJornalPorEmpleado();
    }

    // ============ PROPIEDADES COMPUTADAS (Catálogos) ============

    public function getLotesProperty()
    {
        if (!isset($this->lotesCache)) {
            $this->lotesCache = Lote::where('estado', 'activo')
                ->orderBy('propietario')
                ->get();
        }
        return $this->lotesCache;
    }

    public function getEmpleadosProperty()
    {
        return Empleado::with('rolLaboral')
            ->whereNull('fecha_fin_actividades')
            ->orderBy('apellido')
            ->get();
    }

    public function getMaquinariasProperty()
    {
        return \App\Models\Maquinaria::with('tipoMaquinaria')
            ->orderBy('modelo')
            ->get();
    }

    public function getChoferesProperty()
    {
        return Chofer::where('estado', true)
            ->orderBy('apellido')
            ->get();
    }

    public function getInsumosProperty()
    {
        return Insumo::conStockYPrecio()
            ->with('unidadMedida')
            ->orderBy('nombre')
            ->get();
    }

    public function getCategoriasMaderaProperty()
    {
        return CategoriaMadera::orderBy('nombre')->get();
    }

    public function getClientesProperty()
    {
        return Cliente::orderBy('razon_social')->get();
    }
}
