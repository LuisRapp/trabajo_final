<?php

namespace App\Http\Livewire;

use App\Models\Mantenimiento;
use App\Models\Maquinaria;
use App\Models\NotificacionSistema;
use App\Models\TipoMantenimiento;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Mantenimientos extends Component
{
    public $mantenimientos;

    public $mantenimiento_id;

    public $id_maquinaria;

    public $id_tipo_mantenimiento;

    public $fecha_inicio;

    public $fecha_programada;

    public $estado;

    public $busqueda = '';

    public $maquinarias;

    public $tipos;

    public $kitPreventivo = [];

    public $tab_activo = 'listado';

    // Modal completar
    public $mostrarModalCompletar = false;

    public $orden_completar_id = null;

    public $orden_completar_info = [];

    public $orden_es_correctivo = false;

    public $fecha_fin_completar;

    public $costo_total_completar;

    public $insumos_usados = [];

    protected function rules()
    {
        return [
            'id_maquinaria' => 'required|exists:maquinarias,id_maquinaria',
            'id_tipo_mantenimiento' => 'required|exists:tipo_mantenimientos,id_tipo_mantenimiento',
            'fecha_inicio' => 'required|date',
            'fecha_programada' => [
                'nullable',
                'date',
                'after_or_equal:fecha_inicio',
                function ($attribute, $value, $fail) {
                    if ($value && $this->mantenimiento_id) {
                        $this->validarFechaProgramadaDentroDeRango($value, $fail);
                    }
                },
            ],
            'estado' => 'required|in:programado,en curso',
        ];
    }

    protected $messages = [
        'id_maquinaria.required' => 'Debe seleccionar una maquinaria.',
        'id_tipo_mantenimiento.required' => 'Debe seleccionar un tipo de mantenimiento.',
        'fecha_inicio.required' => 'La fecha de inicio es obligatoria.',
        'estado.required' => 'El estado es obligatorio.',
    ];

    public function mount()
    {
        $this->maquinarias = Maquinaria::where('estado', '!=', 'dado_de_baja')->orderBy('modelo')->get();
        $this->tipos = TipoMantenimiento::orderBy('nombre')->get();
        $this->fecha_inicio = date('Y-m-d');
        $this->estado = 'programado';
        $this->tab_activo = 'listado';
    }

    public function updatedIdMaquinaria()
    {
        $this->cargarKitPreventivo();
    }

    public function updatedIdTipoMantenimiento()
    {
        $this->cargarKitPreventivo();
    }

    public function cargarKitPreventivo()
    {
        $this->kitPreventivo = [];

        if ($this->id_maquinaria && $this->id_tipo_mantenimiento) {
            $tipo = TipoMantenimiento::find($this->id_tipo_mantenimiento);

            if ($tipo && str_contains(strtolower($tipo->nombre), 'preventivo')) {
                $this->kitPreventivo = \App\Models\KitMantenimientoPreventivo::where('kit_mantenimiento_preventivo.id_maquinaria', $this->id_maquinaria)
                    ->join('insumos', 'kit_mantenimiento_preventivo.id_insumo', '=', 'insumos.id_insumo')
                    ->select('insumos.nombre', 'kit_mantenimiento_preventivo.cantidad_requerida')
                    ->get()
                    ->toArray();
            }
        }
    }

    public function render()
    {
        $this->cargarMantenimientos();

        return view('livewire.mantenimientos');
    }

    public function cargarMantenimientos()
    {
        // Primero, marcar como vencidos los mantenimientos programados cuya fecha programada ya pasó
        Mantenimiento::where('estado', 'programado')
            ->whereNotNull('fecha_programada')
            ->where('fecha_programada', '<', now()->toDateString())
            ->update(['estado' => 'vencido']);

        $query = Mantenimiento::with(['maquinaria', 'tipoMantenimiento']);

        if ($this->busqueda) {
            $busq = $this->busqueda;
            $query->where(function ($q) use ($busq) {
                $q->where('estado', 'ILIKE', '%'.$busq.'%')
                    ->orWhereRaw('CAST(costo_total AS TEXT) ILIKE ?', ['%'.$busq.'%'])
                    ->orWhereHas('maquinaria', function ($qm) use ($busq) {
                        $qm->where('modelo', 'ILIKE', '%'.$busq.'%');
                    })
                    ->orWhereHas('tipoMantenimiento', function ($qt) use ($busq) {
                        $qt->where('nombre', 'ILIKE', '%'.$busq.'%');
                    });
            });
        }

        $this->mantenimientos = $query->orderBy('id_mantenimiento', 'desc')->get();
    }

    public function updatedBusqueda()
    {
        $this->cargarMantenimientos();
    }

    public function guardar()
    {
        // Si hay fecha_programada, validar que esté dentro del rango de la notificación
        if ($this->fecha_programada && ! $this->mantenimiento_id) {
            $fail = function ($message) {
                $this->addError('fecha_programada', $message);
            };
            $this->validarFechaProgramadaDentroDeRangoNuevo($this->fecha_programada, $fail);

            if ($this->getErrorBag()->has('fecha_programada')) {
                return;
            }
        }

        $this->validate();

        // La fecha de inicio debe ser igual a la programada si existe
        $fechaInicio = $this->fecha_programada ?: $this->fecha_inicio;
        $mantenimiento = Mantenimiento::create([
            'id_maquinaria' => $this->id_maquinaria,
            'id_tipo_mantenimiento' => $this->id_tipo_mantenimiento,
            'fecha_inicio' => $fechaInicio,
            'fecha_programada' => $this->fecha_programada,
            'estado' => $this->estado,
        ]);

        // Marcar notificación como accionada si existe
        $this->marcarNotificacionComoAccionada($mantenimiento->id_mantenimiento);

        $tipoNombre = TipoMantenimiento::find($this->id_tipo_mantenimiento)->nombre ?? 'Mantenimiento';
        $maquinaNombre = Maquinaria::find($this->id_maquinaria)->modelo ?? '';

        session()->flash('message', "Orden de {$tipoNombre} creada correctamente para {$maquinaNombre}.");
        $this->resetCampos();
        $this->activeTab = 'listado';
        $this->dispatch('mantenimientoGuardado');
    }

    public function editar($id)
    {
        $mantenimiento = Mantenimiento::findOrFail($id);
        $this->mantenimiento_id = $mantenimiento->id_mantenimiento;
        $this->id_maquinaria = $mantenimiento->id_maquinaria;
        $this->id_tipo_mantenimiento = $mantenimiento->id_tipo_mantenimiento;
        $this->fecha_inicio = $mantenimiento->fecha_inicio;
        $this->fecha_programada = $mantenimiento->fecha_programada;
        $this->estado = $mantenimiento->estado;
        $this->activeTab = 'nuevo';
    }

    public function eliminar($id)
    {
        Mantenimiento::findOrFail($id)->delete();
        session()->flash('message', 'Mantenimiento eliminado correctamente.');
    }

    public function resetCampos()
    {
        $this->reset(['mantenimiento_id', 'id_maquinaria', 'id_tipo_mantenimiento', 'fecha_programada']);
        $this->fecha_inicio = date('Y-m-d');
        $this->estado = 'programado';
        $this->kitPreventivo = [];
    }

    public function ejecutarFlujoPresentacion()
    {
        try {
            $params = [
                '--forzar-flujo' => true,
                '--simular' => true,
            ];

            if (! empty($this->id_maquinaria)) {
                $params['--maquinaria'] = (int) $this->id_maquinaria;
            }

            $exitCode = Artisan::call('mantenimiento:check-umbrales', $params);

            $mensaje = $exitCode === 0
                ? 'Flujo de presentacion ejecutado correctamente (orden, asignacion y compra si aplica).'
                : 'El flujo de presentacion finalizo con advertencias. Revisar logs.';
            session()->flash('message', $mensaje);
        } catch (\Throwable $e) {
            session()->flash('error', 'Error al ejecutar flujo de presentacion: '.$e->getMessage());
        }
    }

    public function ejecutarDemo()
    {
        $this->ejecutarFlujoPresentacion();
    }

    public function abrirModalCompletar($id)
    {
        $orden = Mantenimiento::with(['maquinaria', 'tipoMantenimiento'])->findOrFail($id);

        $this->orden_completar_id = $orden->id_mantenimiento;
        $this->orden_completar_info = [
            'id' => $orden->id_mantenimiento,
            'maquinaria' => $orden->maquinaria?->modelo ?? 'N/A',
            'tipo' => $orden->tipoMantenimiento?->nombre ?? 'N/A',
            'fecha_inicio' => $orden->fecha_inicio,
        ];

        $this->fecha_fin_completar = date('Y-m-d');
        $this->costo_total_completar = null;
        $this->insumos_usados = [];

        $this->orden_es_correctivo = str_contains(strtolower($this->orden_completar_info['tipo']), 'correctivo');

        // Siempre mostrar la sección de insumos
        // Si es preventivo, cargar los insumos del kit automáticamente
        if (! $this->orden_es_correctivo) {
            // Cargar insumos del kit de mantenimiento preventivo
            $kitInsumos = \App\Models\KitMantenimientoPreventivo::where('id_maquinaria', $orden->id_maquinaria)
                ->join('insumos', 'kit_mantenimiento_preventivo.id_insumo', '=', 'insumos.id_insumo')
                ->select(
                    'kit_mantenimiento_preventivo.id_insumo',
                    'kit_mantenimiento_preventivo.cantidad_requerida',
                    'insumos.nombre'
                )
                ->get();

            if ($kitInsumos->count() > 0) {
                foreach ($kitInsumos as $item) {
                    $this->insumos_usados[] = [
                        'id_insumo' => $item->id_insumo,
                        'cantidad' => $item->cantidad_requerida,
                    ];
                }
            } else {
                // Si no hay kit, mostrar un campo vacío
                $this->insumos_usados = [['id_insumo' => '', 'cantidad' => '']];
            }
        } else {
            // Para correctivos, iniciar con un campo vacío
            $this->insumos_usados = [['id_insumo' => '', 'cantidad' => '']];
        }

        $this->activeTab = 'listado';
        $this->mostrarModalCompletar = true;
        $this->dispatch('modal-completar-opened', id: $id);
    }

    public function cerrarModalCompletar()
    {
        $this->mostrarModalCompletar = false;
        $this->reset(['orden_completar_id', 'orden_completar_info', 'orden_es_correctivo', 'fecha_fin_completar', 'costo_total_completar', 'insumos_usados']);
    }

    public function agregarInsumo()
    {
        $this->insumos_usados[] = ['id_insumo' => '', 'cantidad' => ''];
    }

    public function eliminarInsumo($index)
    {
        unset($this->insumos_usados[$index]);
        $this->insumos_usados = array_values($this->insumos_usados);
    }

    public function updatedInsumosUsados($value, $key)
    {
        // Ya no necesitamos cargar el precio_unitario porque usamos FIFO
        // Este método puede quedar vacío o eliminarse, pero lo mantenemos por compatibilidad
    }

    public function completarOrden()
    {
        try {
            \Log::info('Iniciando completarOrden', ['orden_id' => $this->orden_completar_id]);

            $orden = Mantenimiento::with(['maquinaria', 'tipoMantenimiento'])->findOrFail($this->orden_completar_id);

            \Log::info('Orden encontrada', ['orden' => $orden->toArray()]);

            $this->validate([
                'fecha_fin_completar' => [
                    'required',
                    'date',
                    function ($attribute, $value, $fail) use ($orden) {
                        if ($value < $orden->fecha_inicio) {
                            $fail('La fecha de finalización no puede ser anterior a la fecha de inicio del mantenimiento.');
                        }
                    },
                ],
                'costo_total_completar' => 'nullable|numeric|min:0',
            ]);

            \Log::info('Validación pasada');

            // Calcular costo total base (mano de obra u otros costos adicionales)
            $costoBase = floatval($this->costo_total_completar ?? 0);

            // Calcular costo de insumos usando FIFO (sin procesar aún, solo calcular)
            $costoInsumos = 0;
            $insumosValidados = [];

            foreach ($this->insumos_usados as $insumo) {
                if (! empty($insumo['id_insumo']) && ! empty($insumo['cantidad'])) {
                    $cantidad = floatval($insumo['cantidad']);

                    // Verificar stock disponible antes de procesar
                    $stockDisponible = \App\Models\MovimientoStock::stockDisponible($insumo['id_insumo']);
                    if ($stockDisponible < $cantidad) {
                        $nombreInsumo = \App\Models\Insumo::find($insumo['id_insumo'])->nombre ?? 'ID '.$insumo['id_insumo'];
                        throw new \Exception("Stock insuficiente para {$nombreInsumo}. Disponible: {$stockDisponible}, Requerido: {$cantidad}");
                    }

                    // Calcular costo FIFO simulado para obtener el total
                    $resultadoSimulado = DB::selectOne(
                        'SELECT * FROM calcular_costo_fifo(?, ?)',
                        [$insumo['id_insumo'], $cantidad]
                    );

                    $costoInsumos += $resultadoSimulado->v_costo_total;
                    $insumosValidados[] = $insumo;
                }
            }

            $costoTotal = $costoBase + $costoInsumos;

            \Log::info('Costo total calculado', ['base' => $costoBase, 'insumos' => $costoInsumos, 'total' => $costoTotal]);

            DB::beginTransaction();

            // Actualizar orden
            $orden->fecha_fin = $this->fecha_fin_completar;
            $orden->costo_total = $costoTotal;
            $orden->estado = 'completado';
            $orden->save();

            \Log::info('Orden actualizada');

            // Registrar todos los insumos usados (preventivo o correctivo)
            $tipoMantenimiento = $this->orden_es_correctivo ? 'Correctivo' : 'Preventivo';

            foreach ($insumosValidados as $insumo) {
                $cantidad = floatval($insumo['cantidad']);
                $motivo = "Mantenimiento {$tipoMantenimiento} - Orden #".$orden->id_mantenimiento;

                // Usar el sistema FIFO para registrar la salida
                $resultadoSalida = \App\Models\MovimientoStock::registrarSalida(
                    $insumo['id_insumo'],
                    $cantidad,
                    $motivo,
                    $this->fecha_fin_completar
                );

                // El costo real viene de los lotes FIFO consumidos
                $costoRealInsumo = $resultadoSalida['costo_total'];

                // Registrar en mantenimiento_insumos con el costo FIFO real
                DB::table('mantenimiento_insumos')->insert([
                    'id_mantenimiento' => $orden->id_mantenimiento,
                    'id_insumo' => $insumo['id_insumo'],
                    'cantidad_utilizada' => $cantidad,
                    'costo_unitario' => $costoRealInsumo / $cantidad, // Promedio ponderado de los lotes
                    'subtotal' => $costoRealInsumo,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            \Log::info('Insumos registrados');

            DB::commit();

            \Log::info('Transacción confirmada');

            session()->flash('message', 'Orden completada exitosamente. Costo total: $'.number_format($costoTotal, 2));

            $this->cerrarModalCompletar();
            $this->cargarMantenimientos();

            \Log::info('Proceso completado exitosamente');

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Error de validación', ['errors' => $e->errors()]);
            $this->addError('general', 'Error de validación: '.implode(', ', array_map(fn ($err) => implode(', ', $err), $e->errors())));
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error completando orden', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            session()->flash('error', 'Error al completar la orden: '.$e->getMessage());
            $this->addError('general', $e->getMessage());
        }
    }

    public function confirmarMantenimiento($id)
    {
        try {
            $mantenimiento = Mantenimiento::findOrFail($id);

            if ($mantenimiento->estado !== 'programado') {
                session()->flash('error', 'Solo se pueden confirmar mantenimientos en estado programado.');

                return;
            }

            $mantenimiento->update([
                'estado' => 'en curso',
                'fecha_inicio' => now()->toDateString(),
            ]);

            // Marcar notificación como accionada
            $this->marcarNotificacionComoAccionada($id);

            session()->flash('message', "Mantenimiento #{$id} confirmado y en curso.");
            $this->cargarMantenimientos();

        } catch (\Exception $e) {
            session()->flash('error', 'Error al confirmar mantenimiento: '.$e->getMessage());
        }
    }

    public function reprogramarMantenimiento($id)
    {
        try {
            $mantenimiento = Mantenimiento::findOrFail($id);

            if ($mantenimiento->estado !== 'vencido') {
                session()->flash('error', 'Solo se pueden reprogramar mantenimientos vencidos.');

                return;
            }

            $mantenimiento->update([
                'estado' => 'programado',
                'fecha_programada' => null,
            ]);

            session()->flash('message', "Mantenimiento #{$id} reprogramado. Por favor, asigne una nueva fecha.");
            $this->editar($id);

        } catch (\Exception $e) {
            session()->flash('error', 'Error al reprogramar: '.$e->getMessage());
        }
    }

    /**
     * Valida que la fecha_programada esté dentro de los 7 días desde la notificación
     * Para mantenimientos existentes (edición)
     */
    protected function validarFechaProgramadaDentroDeRango($fechaProgramada, $fail)
    {
        $notificacion = NotificacionSistema::where('mantenimiento_id', $this->mantenimiento_id)
            ->where('tipo', 'umbral_alcanzado')
            ->orderBy('created_at', 'desc')
            ->first();

        if (! $notificacion) {
            return; // Si no hay notificación, no validar (puede ser mantenimiento creado manualmente)
        }

        $fechaNotificacion = $notificacion->created_at->toDateString();
        $fechaLimite = $notificacion->created_at->addDays(7)->toDateString();

        if ($fechaProgramada < $fechaNotificacion || $fechaProgramada > $fechaLimite) {
            $fail("La fecha programada debe estar entre {$fechaNotificacion} y {$fechaLimite} (dentro de los 7 días desde la notificación).");
        }
    }

    /**
     * Valida que la fecha_programada esté dentro de los 7 días desde HOY
     * Para mantenimientos nuevos (sin notificación previa)
     */
    protected function validarFechaProgramadaDentroDeRangoNuevo($fechaProgramada, $fail)
    {
        $fechaNotificacion = now()->toDateString();
        $fechaLimite = now()->addDays(7)->toDateString();

        if ($fechaProgramada < $fechaNotificacion || $fechaProgramada > $fechaLimite) {
            $fail("La fecha programada debe estar entre {$fechaNotificacion} y {$fechaLimite} (dentro de los próximos 7 días).");
        }
    }

    /**
     * Marca como accionada la notificación del usuario actual relacionada con este mantenimiento
     */
    protected function marcarNotificacionComoAccionada($mantenimientoId)
    {
        try {
            $notificacion = NotificacionSistema::where('user_id', Auth::id())
                ->where('mantenimiento_id', $mantenimientoId)
                ->where('accionada', false)
                ->first();

            if ($notificacion) {
                $notificacion->marcarComoAccionada();
                \Log::info("Notificación #{$notificacion->id} marcada como accionada para mantenimiento #{$mantenimientoId}");
            }
        } catch (\Exception $e) {
            \Log::warning("Error marcando notificación como accionada: {$e->getMessage()}");
            // No lanzar excepción para no interrumpir el flujo principal
        }
    }
}
