<?php

namespace App\Http\Livewire;

use App\Http\Livewire\Traits\MensajesErrorUsuario;
use App\Models\Mantenimiento;
use App\Models\Maquinaria;
use App\Models\NotificacionSistema;
use App\Models\TipoMantenimiento;
use App\Services\MantenimientoService;
use App\Services\NotificacionService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Mantenimientos extends Component
{
    use MensajesErrorUsuario;

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

        $servicio = app(MantenimientoService::class);
        $fechaInicio = $this->fecha_programada ?: $this->fecha_inicio;
        $mantenimiento = $servicio->crearMantenimiento([
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
        $this->tab_activo = 'listado';
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
        $this->tab_activo = 'nuevo';
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
            session()->flash('error', $this->mensajeErrorUsuario($e, 'ejecutar el flujo'));
        }
    }

    public function ejecutarDemo()
    {
        $this->ejecutarFlujoPresentacion();
    }

    public function abrirModalCompletar($id)
    {
        $this->dispatch('abrirCompletarOrden', $id);
    }

    public function onOrdenCompletada(): void
    {
        $this->cargarMantenimientos();
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
            session()->flash('error', $this->mensajeErrorUsuario($e, 'confirmar el mantenimiento'));
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
            session()->flash('error', $this->mensajeErrorUsuario($e, 'reprogramar el mantenimiento'));
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
                NotificacionService::marcarComoAccionada($notificacion);
                \Log::info("Notificación #{$notificacion->id} marcada como accionada para mantenimiento #{$mantenimientoId}");
            }
        } catch (\Exception $e) {
            \Log::warning("Error marcando notificación como accionada: {$e->getMessage()}");
            // No lanzar excepción para no interrumpir el flujo principal
        }
    }
}
