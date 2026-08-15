<?php

namespace App\Http\Livewire;

use App\Enums\TaskType;
use App\Events\CargaRegistrada;
use App\Http\Livewire\Traits\MensajesErrorUsuario;
use App\Models\Carga;
use App\Models\CategoriaMadera;
use App\Models\Chofer;
use App\Models\Cliente;
use App\Models\Empleado;
use App\Models\Lote;
use App\Models\LoteTarea;
use App\Models\MovimientoStock;
use App\Models\ParteDiario;
use App\Services\ClimaOperativoService;
use App\Services\InventarioService;
use App\Services\ParteDiarioCostoService;
use App\Services\PartesDiariosService;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;

class PartesDiarios extends Component
{
    use MensajesErrorUsuario;
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    // Parte Diario Principal
    // Listado paginado se entrega desde render(), no como estado
    public $parte_id;

    public $id_lote;

    public $id_lote_tarea;

    public $fecha;

    public $tipo_tarea;

    public $actividad_realizada;

    public $es_dia_caido = false;

    public $motivo_dia_caido;

    public $clima_estado;

    public $clima_razon;

    public $clima_fuente;

    public $clima_es_fin_de_semana = false;

    public $clima_requiere_override = false;

    public $clima_override_confirmado = false;

    public $clima_override_motivo;

    public $observaciones;

    public $busqueda = '';

    public $busqueda_fecha = '';

    public $busqueda_empleado = '';

    public $tab_activo = 'listado';

    // Catálogos (lazy loaded via computed properties)
    protected $lotesCache;

    protected $empleadosFiltradosCache;

    // Catálogos pesados se obtienen vía propiedades computadas para evitar deshidratación
    public $empleados_asignados_ids = [];

    // Se usa propiedad computada
    public $maquinarias_asignadas_ids = [];

    // Detalles de Cargas (Modo Destajo)
    public $cargas = [];

    public $total_toneladas = 0;

    // Detalles de Jornales (Modo Día Caído) — managed by JornalForm child
    public $jornales = [];

    // Detalles de Movimientos de Insumos — managed by MovimientoForm child
    public $movimientos = [];

    // Creación rápida de tarea
    public $mostrarModalTareaRapida = false;

    public $nueva_tarea_tipo_tarea;

    public $nueva_tarea_superficie_afectada_ha;

    protected function rules()
    {
        $fechaMinima = Carbon::today()->subDays(7)->toDateString();

        $rules = [
            'id_lote' => 'required|exists:lotes,id_lote',
            'id_lote_tarea' => [
                'required',
                'integer',
                \Illuminate\Validation\Rule::exists('lote_tareas', 'id_lote_tarea')->where(function ($q) {
                    $q->where('id_lote', $this->id_lote);
                }),
            ],
            // Validar fecha: solo hoy o dentro de los últimos 7 días
            'fecha' => [
                'required',
                'date',
                'before_or_equal:today',
                'after_or_equal:'.$fechaMinima,
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
            'id_lote_tarea.required' => 'Debe seleccionar una tarea del lote.',
            'id_lote_tarea.exists' => 'La tarea seleccionada no es válida para este lote.',
        ];
    }

    public function getTaskTypesProperty(): array
    {
        return TaskType::cases();
    }

    public function getLoteTareasProperty()
    {
        if (! $this->id_lote) {
            return collect();
        }

        return LoteTarea::query()
            ->where('id_lote', $this->id_lote)
            ->orderByRaw("CASE WHEN estado IN ('en_ejecucion','planificada') THEN 0 ELSE 1 END")
            ->orderByDesc('id_lote_tarea')
            ->get();
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
        unset($this->empleadosFiltradosCache);

        // Reset búsqueda de empleados
        $this->busqueda_empleado = '';

        // Reset tarea seleccionada / creación rápida
        $this->id_lote_tarea = null;
        $this->nueva_tarea_tipo_tarea = null;
        $this->nueva_tarea_superficie_afectada_ha = null;

        $this->revisarClima();

        // Notificar al componente hijo CargaForm
        $this->dispatch('loteChanged');
    }

    public function crearTareaRapida()
    {
        if (! $this->id_lote) {
            session()->flash('error', 'Seleccione un lote antes de crear una tarea.');

            return;
        }

        $taskType = TaskType::tryFrom((string) $this->nueva_tarea_tipo_tarea);
        if (! $taskType) {
            session()->flash('error', 'Seleccione un tipo de tarea válido.');

            return;
        }

        $superficie = $this->nueva_tarea_superficie_afectada_ha;
        if ($superficie !== null && $superficie !== '') {
            $superficie = (float) $superficie;
            if ($superficie <= 0) {
                session()->flash('error', 'La superficie afectada debe ser mayor a 0.');

                return;
            }
        } else {
            $superficie = null;
        }

        try {
            $tarea = LoteTarea::create([
                'id_lote' => $this->id_lote,
                'tipo_tarea' => $taskType->value,
                'estado' => 'en_ejecucion',
                'fecha_inicio' => $this->fecha ?: now()->toDateString(),
                'superficie_afectada_ha' => $superficie,
                'observaciones' => 'Creada desde Parte Diario',
            ]);

            $this->id_lote_tarea = $tarea->id_lote_tarea;
            $this->nueva_tarea_tipo_tarea = null;
            $this->nueva_tarea_superficie_afectada_ha = null;
            $this->mostrarModalTareaRapida = false;

            session()->flash('message', 'Tarea creada y seleccionada.');
            $this->dispatch('$refresh');
        } catch (\Exception $e) {
            session()->flash('error', $this->mensajeErrorUsuario($e, 'crear la tarea rápida'));
            \Log::error('Error al crear tarea rápida', [
                'lote' => $this->id_lote,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function getEmpleadosFiltradosProperty()
    {
        if (isset($this->empleadosFiltradosCache)) {
            return $this->empleadosFiltradosCache;
        }

        $empleados = $this->empleados; // propiedad computada
        if (! empty($this->empleados_asignados_ids)) {
            $empleados = $empleados->filter(function ($emp) {
                return in_array($emp->id_empleado, $this->empleados_asignados_ids);
            });
        }

        if (! empty($this->busqueda_empleado)) {
            $busq = strtolower($this->busqueda_empleado);
            $empleados = $empleados->filter(function ($emp) use ($busq) {
                return str_contains(strtolower($emp->apellido.' '.$emp->nombre), $busq);
            });
        }

        $this->empleadosFiltradosCache = $empleados;

        return $this->empleadosFiltradosCache;
    }

    public function render()
    {
        $query = ParteDiario::with('lote')->orderBy('fecha', 'desc')->orderBy('id_parte_diario', 'desc');

        // Buscar por propietario del lote
        if ($this->busqueda) {
            $busq = $this->busqueda;
            $query->whereHas('lote', function ($ql) use ($busq) {
                $ql->where('propietario', 'ILIKE', '%'.$busq.'%');
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

    public function updatedBusquedaEmpleado()
    {
        unset($this->empleadosFiltradosCache);
    }

    public function updatedFecha()
    {
        $this->revisarClima();
    }

    public function updatedEsDiaCaido()
    {
        // Limpiar datos de la sección no activa
        if ($this->es_dia_caido) {
            $this->cargas = [];
            $this->total_toneladas = 0;
            $this->clima_override_confirmado = false;
            $this->clima_override_motivo = '';
        } else {
            $this->jornales = [];
            $this->motivo_dia_caido = '';
        }
    }

    private function revisarClima(): void
    {
        $this->clima_estado = null;
        $this->clima_razon = null;
        $this->clima_fuente = null;
        $this->clima_es_fin_de_semana = false;
        $this->clima_requiere_override = false;

        if (! $this->id_lote || ! $this->fecha) {
            $this->clima_override_confirmado = false;
            $this->clima_override_motivo = '';

            return;
        }

        $fecha = Carbon::parse($this->fecha);
        $this->clima_es_fin_de_semana = $fecha->isWeekend();

        $lote = Lote::find($this->id_lote);
        if (! $lote) {
            return;
        }

        $climaDia = app(ClimaOperativoService::class)->obtenerEstadoDia($lote, $fecha);
        $estadoPronostico = $climaDia->estado_pronostico ?? $climaDia->estado_operativo ?? 'OPERATIVO';
        $this->clima_estado = strtoupper((string) $estadoPronostico);
        $this->clima_razon = $climaDia->razon_pronostico ?? $climaDia->razon ?? null;
        $this->clima_fuente = $climaDia->fuente_pronostico ?? $climaDia->fuente ?? null;

        $noOperativo = $this->clima_estado === 'INACTIVO';
        $this->clima_requiere_override = $noOperativo || $this->clima_es_fin_de_semana;

        if (! $this->clima_requiere_override) {
            $this->clima_override_confirmado = false;
            $this->clima_override_motivo = '';
        }
    }

    private function resolverClimaOperacion(): array
    {
        if (! $this->id_lote || ! $this->fecha) {
            return [
                'requiere_override' => false,
                'estado' => null,
                'razon' => null,
                'fuente' => null,
            ];
        }

        $fecha = Carbon::parse($this->fecha);
        $esFinDeSemana = $fecha->isWeekend();

        $lote = Lote::find($this->id_lote);
        if (! $lote) {
            return [
                'requiere_override' => $esFinDeSemana,
                'estado' => null,
                'razon' => null,
                'fuente' => null,
            ];
        }

        $climaDia = app(ClimaOperativoService::class)->obtenerEstadoDia($lote, $fecha);
        $estado = strtoupper((string) ($climaDia->estado_pronostico ?? $climaDia->estado_operativo ?? 'OPERATIVO'));
        $requiere = $esFinDeSemana || $estado === 'INACTIVO';

        return [
            'requiere_override' => $requiere,
            'estado' => $estado,
            'razon' => $climaDia->razon ?? null,
            'fuente' => $climaDia->fuente ?? null,
        ];
    }

    // ============ GESTIÓN DE CARGAS (DESTAJO) — Event Listeners ============

    public function cargaAgregada(array $cargaData): void
    {
        $this->cargas[] = $cargaData;
        $this->calcularTotalToneladas();
    }

    public function cargaEliminada(int $index): void
    {
        unset($this->cargas[$index]);
        $this->cargas = array_values($this->cargas);
        $this->calcularTotalToneladas();
    }

    private function calcularTotalToneladas(): void
    {
        $this->total_toneladas = array_sum(array_column($this->cargas, 'peso_neto'));
    }

    // ============ GESTIÓN DE JORNALES (DÍA CAÍDO) ============
    // Methods extracted to JornalForm child component

    private function obtenerJornalEmpleadoParaFecha($empleadoId, $fecha)
    {
        if (! $empleadoId || ! $fecha) {
            return null;
        }
        $empleado = $this->empleados->firstWhere('id_empleado', $empleadoId);
        if (! $empleado || ! $empleado->rolLaboral) {
            return null;
        }
        $rolId = $empleado->rolLaboral->id_rol_laboral ?? $empleado->id_rol_laboral ?? null;
        if (! $rolId) {
            return null;
        }
        $hist = \App\Models\HistoricoRolLaboral::where('rol_laboral_id', $rolId)
            ->vigenteEnFecha($fecha)
            ->first();
        if ($hist) {
            return (float) ($hist->jornal_diario ?? 0);
        }

        // Fallback al valor actual del rol si no hay histórico
        return (float) ($empleado->rolLaboral->jornal_diario ?? 0);
    }

    // ============ GESTIÓN DE MOVIMIENTOS DE INSUMOS ============
    // Methods extracted to MovimientoForm child component

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

        // Validación de clima
        $climaInfo = $this->resolverClimaOperacion();
        $this->clima_requiere_override = (bool) ($climaInfo['requiere_override'] ?? false);

        if (! $this->es_dia_caido && $this->clima_requiere_override) {
            if (! $this->clima_override_confirmado) {
                session()->flash('error', 'El dia seleccionado esta marcado como no operativo. Debe confirmar la operacion para continuar.');

                return;
            }
            if (! trim((string) $this->clima_override_motivo)) {
                session()->flash('error', 'Debe indicar un motivo de confirmacion para operar en dia no operativo.');

                return;
            }
        }

        // Validaciones adicionales
        if (! $this->es_dia_caido && empty($this->cargas)) {
            session()->flash('error', 'Debe registrar al menos una carga para modo producción.');

            return;
        }

        if (! $this->es_dia_caido) {
            foreach ($this->cargas as $idx => $c) {
                $maqs = $c['maquinarias'] ?? [];
                if (empty($maqs)) {
                    session()->flash('error', 'La carga #'.($idx + 1).' no tiene maquinarias seleccionadas. Asigne al menos una maquinaria.');

                    return;
                }
            }
        }

        if ($this->es_dia_caido && empty($this->jornales)) {
            session()->flash('error', 'Debe agregar al menos un empleado para el jornal por día caído.');

            return;
        }

        try {
            $resultado = PartesDiariosService::guardar([
                'parte_id' => $this->parte_id,
                'id_lote' => $this->id_lote,
                'id_lote_tarea' => $this->id_lote_tarea,
                'fecha' => $this->fecha,
                'es_dia_caido' => $this->es_dia_caido,
                'clima_override_confirmado' => $this->clima_override_confirmado,
                'clima_override_motivo' => $this->clima_override_motivo,
                'observaciones' => $this->observaciones,
                'cargas' => $this->cargas,
                'jornales' => $this->jornales,
                'movimientos' => $this->movimientos,
            ]);

            $parteDiario = $resultado['parte_diario'];
            $this->parte_id = $parteDiario->id_parte_diario;

            // Despachar eventos post-commit
            foreach ($resultado['eventos_carga'] as [$carga, $maqId, $ton]) {
                event(new CargaRegistrada($carga, $maqId, $ton));
            }

            // Calcular y guardar costos del parte diario
            try {
                ParteDiarioCostoService::calcularYGuardarCostos($parteDiario);
            } catch (\Exception $e) {
                \Log::error('Error al calcular costos del parte diario', [
                    'parte_id' => $parteDiario->id_parte_diario,
                    'error' => $e->getMessage(),
                ]);
            }

            $mensaje = $resultado['es_nuevo']
                ? 'Parte diario creado correctamente con todos sus detalles.'
                : 'Parte diario actualizado correctamente con todos sus detalles.';

            $this->resetCampos();
            session()->flash('message', $mensaje);

        } catch (\Exception $e) {
            session()->flash('error', $this->mensajeErrorUsuario($e, 'guardar el parte diario'));
            \Log::error('Error en PartesDiarios::guardar()', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    public function editar($id)
    {
        $parte = ParteDiario::with(['empleados.rolLaboral'])->findOrFail($id);
        $this->parte_id = $parte->id_parte_diario;
        $this->id_lote = $parte->id_lote;
        $this->id_lote_tarea = $parte->id_lote_tarea;
        $this->fecha = $parte->fecha;
        $this->tipo_tarea = $parte->tipo_tarea;
        $this->es_dia_caido = (bool) $parte->es_dia_caido;
        $this->observaciones = $parte->observaciones;
        $this->clima_override_confirmado = (bool) ($parte->clima_override ?? false);
        $this->clima_override_motivo = $parte->clima_override_motivo;
        $this->tab_activo = 'nuevo';

        // Cargar CARGAS si es producción
        $this->cargas = [];
        if (! $this->es_dia_caido) {
            $cargas = Carga::with(['empleados', 'maquinarias', 'cliente'])
                ->where('id_parte_diario', $parte->id_parte_diario)
                ->get();

            foreach ($cargas as $c) {
                $this->cargas[] = [
                    'id_categoria_madera' => $c->id_categoria_madera,
                    'ticket' => $c->ticket,
                    'peso_bruto' => (float) $c->peso_bruto,
                    'tara' => (float) $c->tara,
                    'peso_neto' => (float) $c->peso_neto,
                    'id_chofer' => $c->id_chofer,
                    'destino' => $c->id_cliente,
                    'destino_nombre' => $c->cliente->razon_social ?? 'Cliente no encontrado',
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
                    'nombre_completo' => $emp->apellido.', '.$emp->nombre,
                    'rol' => $emp->rolLaboral->nombre ?? 'N/A',
                    'jornal_diario' => $jornalVig,
                    'observaciones' => null,
                ];
            }
        }

        // Cargar MOVIMIENTOS vinculados a este parte (por motivo y fecha)
        $this->movimientos = [];
        $movs = MovimientoStock::delParteDiario($parte->id_parte_diario, $this->fecha)
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
            $clave = $m->id_insumo.'_'.$m->tipo.'_'.$motivoEnum;

            if (! isset($movimientosAgrupados[$clave])) {
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

        $this->revisarClima();
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
            'parte_id', 'id_lote', 'id_lote_tarea', 'fecha', 'tipo_tarea', 'actividad_realizada', 'es_dia_caido',
            'motivo_dia_caido', 'clima_estado', 'clima_razon', 'clima_fuente', 'clima_es_fin_de_semana',
            'clima_requiere_override', 'clima_override_confirmado', 'clima_override_motivo',
            'observaciones', 'cargas', 'jornales', 'movimientos',
            'empleados_asignados_ids', 'maquinarias_asignadas_ids',
            'nueva_tarea_tipo_tarea', 'nueva_tarea_superficie_afectada_ha',
        ]);
        $this->total_toneladas = 0;
    }

    public function cancelarEdicion()
    {
        $this->resetCampos();
        $this->resetValidation();
        $this->tab_activo = 'listado';
        $this->dispatch('parteDiarioCancelado');
    }

    // ============ EVENT LISTENERS (Child Components) ============

    public function onJornalAgregado($jornalData): void
    {
        $this->jornales[] = $jornalData;
    }

    public function onJornalEliminado($index): void
    {
        unset($this->jornales[$index]);
        $this->jornales = array_values($this->jornales);
    }

    public function onMovimientoAgregado($movData): void
    {
        $this->movimientos[] = $movData;
    }

    public function onMovimientoEliminado($index): void
    {
        unset($this->movimientos[$index]);
        $this->movimientos = array_values($this->movimientos);
    }

    // ============ PROPIEDADES COMPUTADAS (Catálogos) ============

    public function getLotesProperty()
    {
        if (! isset($this->lotesCache)) {
            $this->lotesCache = Lote::whereIn('estado', ['activo', 'en_proceso'])
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
        return InventarioService::queryInsumosConStockYPrecio()
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
