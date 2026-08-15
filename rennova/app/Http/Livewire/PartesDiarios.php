<?php

namespace App\Http\Livewire;

use App\Enums\TaskType;
use App\Events\CargaRegistrada;
use App\Http\Livewire\Traits\CatalogosTrait;
use App\Http\Livewire\Traits\ClimaOperativoTrait;
use App\Http\Livewire\Traits\JornalLookupTrait;
use App\Http\Livewire\Traits\MensajesErrorUsuario;
use App\Models\Lote;
use App\Models\LoteTarea;
use App\Models\ParteDiario;
use App\Services\ParteDiarioCostoService;
use App\Services\PartesDiariosService;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;

class PartesDiarios extends Component
{
    use CatalogosTrait;
    use ClimaOperativoTrait;
    use JornalLookupTrait;
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

        $this->resolverClima();

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
        $this->resolverClima();
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

    // ============ GESTIÓN DE MOVIMIENTOS DE INSUMOS ============
    // Methods extracted to MovimientoForm child component

    public function validarPaso1(): bool
    {
        $this->resetValidation();
        $this->validate([
            'id_lote' => 'required',
            'id_lote_tarea' => 'required',
            'fecha' => 'required|date',
        ]);

        return true;
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

        // Validación de clima
        $climaInfo = $this->resolverClima();
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

    public function editar($id): void
    {
        $datos = PartesDiariosService::cargarParaEdicion($id);

        $this->parte_id = $datos['parte_id'];
        $this->id_lote = $datos['id_lote'];
        $this->id_lote_tarea = $datos['id_lote_tarea'];
        $this->fecha = $datos['fecha'];
        $this->es_dia_caido = $datos['es_dia_caido'];
        $this->observaciones = $datos['observaciones'];
        $this->clima_override_confirmado = $datos['clima_override_confirmado'];
        $this->clima_override_motivo = $datos['clima_override_motivo'];
        $this->cargas = $datos['cargas'];
        $this->jornales = $datos['jornales'];
        $this->movimientos = $datos['movimientos'];
        $this->total_toneladas = $datos['total_toneladas'];

        $this->tab_activo = 'nuevo';
        $this->dispatch('editarParte', parteId: $id);
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
}
