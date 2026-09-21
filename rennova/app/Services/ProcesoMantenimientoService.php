<?php

namespace App\Services;

use App\Models\Empleado;
use App\Models\Lote;
use App\Models\Mantenimiento;
use App\Models\Maquinaria;
use App\Models\NotificacionSistema;
use App\Models\TipoMantenimiento;
use App\Models\Usuario;
use App\Notifications\MantenimientoProgramadoRecordatorio;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

/**
 * Proceso automático de mantenimiento preventivo (PA-01).
 *
 * Casos de uso del proceso que corre sin intervención humana: verificación
 * diaria de umbrales de producción por maquinaria, generación de órdenes
 * con fecha resuelta por clima, recordatorios de órdenes programadas y
 * marcado de vencidos con notificación interna de respaldo.
 *
 * El ciclo de vida interactivo (crear, aprobar, completar, reprogramar)
 * vive en MantenimientoService; los comandos de consola orquestan este
 * servicio.
 */
class ProcesoMantenimientoService
{
    private const CLIMA_VENTANA_HORAS = 72;

    public function __construct(
        private readonly MantenimientoService $mantenimientoService,
        private readonly ClimaDecisionService $climaDecisionService,
        private readonly PropuestaCompraMantenimientoService $propuestaCompraService,
        private readonly MailMantenimientoService $mailService,
    ) {}

    /**
     * Maquinarias operativas con umbral configurado, candidatas a verificación.
     *
     * @return Collection<int, Maquinaria>
     */
    public function obtenerMaquinariasElegibles(?int $maquinariaId = null): Collection
    {
        return Maquinaria::query()
            ->with('tipoMaquinaria')
            ->whereNotNull('umbral_toneladas')
            ->whereIn('estado', ['operativa', 'activo'])
            ->when($maquinariaId, fn ($q) => $q->where('id_maquinaria', $maquinariaId))
            ->get();
    }

    /**
     * Tipo de mantenimiento preventivo del catálogo.
     *
     * Tipa las órdenes que el proceso genera por umbral.
     */
    public function obtenerTipoPreventivo(): ?TipoMantenimiento
    {
        return TipoMantenimiento::query()
            ->whereLike('nombre', '%preventivo%')
            ->first();
    }

    /**
     * Caso de uso del proceso automático por umbral para una maquinaria.
     *
     * Si supera el umbral y no tiene orden abierta: resuelve la fecha por clima,
     * crea la orden en transacción (personal, stock del kit, propuesta de compra
     * y notificación interna) y envía el email con adjuntos después del commit.
     *
     * @param  Maquinaria  $maquinaria  Maquinaria a verificar
     * @param  TipoMantenimiento  $tipoPreventivo  Tipo preventivo del catálogo
     * @return array{generada: bool, motivo?: string, toneladas?: float, umbral?: float, mantenimiento?: Mantenimiento, programacion?: array, falta_stock?: bool, insumos?: array}
     *
     * @throws \Throwable Si falla la transacción de creación de la orden
     */
    public function procesarUmbralMaquinaria(Maquinaria $maquinaria, TipoMantenimiento $tipoPreventivo): array
    {
        $toneladasDesdeUltimo = $this->obtenerToneladasDesdeUltimoMantenimiento($maquinaria);
        $umbral = (float) $maquinaria->umbral_toneladas;

        if ($toneladasDesdeUltimo < $umbral) {
            return [
                'generada' => false,
                'motivo' => 'bajo_umbral',
                'toneladas' => $toneladasDesdeUltimo,
                'umbral' => $umbral,
            ];
        }

        if ($this->tieneOrdenAbierta($maquinaria)) {
            return ['generada' => false, 'motivo' => 'orden_abierta'];
        }

        $programacion = $this->resolverFechaProgramadaPorClima($maquinaria);

        [$faltaStock, $insumosConProblema, $propuesta, $mantenimiento] = DB::transaction(function () use ($maquinaria, $tipoPreventivo, $programacion, $toneladasDesdeUltimo) {
            $mantenimiento = $this->mantenimientoService->crearMantenimiento([
                'id_maquinaria' => $maquinaria->id_maquinaria,
                'id_tipo_mantenimiento' => $tipoPreventivo->id_tipo_mantenimiento,
                'fecha_inicio' => $programacion['fecha_programada']->toDateString(),
                'fecha_programada' => $programacion['fecha_programada']->toDateString(),
                'estado' => 'programado',
            ]);

            $asignacion = $this->asignarPersonalAutomatico(
                mantenimiento: $mantenimiento,
                fechaProgramada: $programacion['fecha_programada']
            );

            $verificacionStock = $this->mantenimientoService->verificarStockParaAprobacion($mantenimiento->id_mantenimiento);
            $faltaStock = ! $verificacionStock['puede_aprobar'];
            $insumosConProblema = $verificacionStock['insuficientes'];

            $propuesta = null;
            if ($faltaStock) {
                $propuesta = $this->propuestaCompraService->crearPropuesta($mantenimiento, $insumosConProblema);
            }

            $this->crearNotificacionInterna(
                mantenimiento: $mantenimiento,
                maquinaria: $maquinaria,
                toneladasDesdeUltimo: $toneladasDesdeUltimo,
                programacion: $programacion,
                asignacion: $asignacion
            );

            return [$faltaStock, $insumosConProblema, $propuesta, $mantenimiento];
        });

        $this->propuestaCompraService->enviarCorreoOrden($mantenimiento, $propuesta);

        return [
            'generada' => true,
            'mantenimiento' => $mantenimiento,
            'programacion' => $programacion,
            'falta_stock' => $faltaStock,
            'insumos' => $insumosConProblema,
        ];
    }

    /**
     * Mantenimientos programados para hoy.
     *
     * @return Collection<int, Mantenimiento>
     */
    public function obtenerProgramadosDeHoy(): Collection
    {
        return Mantenimiento::with(['maquinaria', 'tipoMantenimiento'])
            ->where('estado', 'programado')
            ->where('fecha_programada', now()->toDateString())
            ->get();
    }

    /**
     * Notificaciones de umbral pendientes de programar con fecha límite cercana.
     *
     * @param  string  $limiteAviso  Fecha límite máxima para el aviso
     * @return Collection<int, NotificacionSistema>
     */
    public function obtenerPendientesDeProgramar(string $limiteAviso): Collection
    {
        return NotificacionSistema::query()
            ->with(['mantenimiento.maquinaria', 'mantenimiento.tipoMantenimiento'])
            ->where('tipo', 'umbral_alcanzado')
            ->whereNotNull('fecha_limite')
            ->where('fecha_limite', '<=', $limiteAviso)
            ->where(function ($q) {
                $q->where('accionada', false)->orWhereNull('accionada');
            })
            ->whereHas('mantenimiento', function ($q) {
                $q->where('estado', 'programado')->whereNull('fecha_programada');
            })
            ->orderByDesc('created_at')
            ->get()
            ->unique('mantenimiento_id')
            ->values();
    }

    /**
     * Marca como vencidos los mantenimientos no confirmados cuya fecha pasó
     * y crea la notificación interna de respaldo para los usuarios configurados.
     *
     * @param  string  $hoy  Fecha de hoy (Y-m-d)
     * @return Collection<int, Mantenimiento> Mantenimientos marcados como vencidos
     */
    public function marcarMantenimientosVencidos(string $hoy): Collection
    {
        $vencidos = Mantenimiento::query()
            ->where('estado', 'programado')
            ->where('fecha_programada', '<', $hoy)
            ->get();

        if ($vencidos->isEmpty()) {
            return $vencidos;
        }

        DB::transaction(function () use ($vencidos) {
            foreach ($vencidos as $mantenimiento) {
                $mantenimiento->update(['estado' => 'vencido']);
                $this->crearNotificacionVencido($mantenimiento);

                Log::warning('Mantenimiento vencido', [
                    'id_mantenimiento' => $mantenimiento->id_mantenimiento,
                    'id_maquinaria' => $mantenimiento->id_maquinaria,
                    'fecha_programada' => $mantenimiento->fecha_programada,
                ]);
            }
        });

        return $vencidos;
    }

    /**
     * Envía el recordatorio de mantenimientos de hoy y pendientes de programar
     * por email a los usuarios configurados (con reintentos).
     *
     * @param  Collection<int, Mantenimiento>  $mantenimientosHoy
     * @param  Collection<int, NotificacionSistema>|null  $pendientesProgramar
     */
    public function enviarRecordatorioProgramados(Collection $mantenimientosHoy, ?Collection $pendientesProgramar = null): void
    {
        try {
            $pendientesProgramar = $pendientesProgramar ?? collect();

            if ($mantenimientosHoy->isEmpty() && $pendientesProgramar->isEmpty()) {
                return;
            }

            $idsUsuarios = app(NotificacionService::class)->cargarConfiguracionMantenimiento()['recordatorio'];

            if (empty($idsUsuarios)) {
                $correoAdmin = config('mail.admin_email', 'admin@example.com');
                $this->mailService->enviar(function () use ($correoAdmin, $mantenimientosHoy, $pendientesProgramar) {
                    Notification::route('mail', $correoAdmin)
                        ->notify(new MantenimientoProgramadoRecordatorio($mantenimientosHoy, $pendientesProgramar));
                });
                Log::info("Recordatorio de mantenimientos enviado a {$correoAdmin} (fallback)");

                return;
            }

            $usuarios = Usuario::whereIn('id', $idsUsuarios)->get();

            foreach ($usuarios as $usuario) {
                $this->mailService->enviar(function () use ($usuario, $mantenimientosHoy, $pendientesProgramar) {
                    $usuario->notify(new MantenimientoProgramadoRecordatorio($mantenimientosHoy, $pendientesProgramar));
                });
            }

            Log::info("Recordatorio de mantenimientos enviado a {$usuarios->count()} usuario(s)");
        } catch (\Throwable $e) {
            Log::error('Error enviando recordatorio de mantenimientos', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Toneladas procesadas por la maquinaria desde su último mantenimiento cerrado.
     */
    private function obtenerToneladasDesdeUltimoMantenimiento(Maquinaria $maquinaria): float
    {
        $ultimoMantenimiento = Mantenimiento::query()
            ->where('id_maquinaria', $maquinaria->id_maquinaria)
            ->whereNotNull('toneladas_snapshot')
            ->orderBy('fecha_fin', 'desc')
            ->first();

        if (! $ultimoMantenimiento) {
            return (float) $maquinaria->toneladas_acumuladas;
        }

        return (float) $maquinaria->toneladas_acumuladas - (float) $ultimoMantenimiento->toneladas_snapshot;
    }

    /**
     * Si la maquinaria ya tiene una orden de mantenimiento abierta.
     */
    private function tieneOrdenAbierta(Maquinaria $maquinaria): bool
    {
        return Mantenimiento::query()
            ->where('id_maquinaria', $maquinaria->id_maquinaria)
            ->whereIn('estado', ['programado', 'en curso'])
            ->exists();
    }

    /**
     * Resuelve la fecha programada del mantenimiento según el clima del lote.
     *
     * Regla:
     * - Si hay lluvia dentro de 72h -> usar ese dia exacto (se trabaja igual por clima).
     * - Si no hay lluvia o faltan datos -> fallback al dia siguiente.
     *
     * @return array{fecha_programada: Carbon, fuente: string, motivo: string, lote_id: ?int, lluvia_mm: ?float}
     */
    private function resolverFechaProgramadaPorClima(Maquinaria $maquinaria): array
    {
        $fallbackDate = now()->addDay()->startOfDay();
        $limite = now()->addHours(self::CLIMA_VENTANA_HORAS);

        $lote = Lote::query()
            ->whereIn('estado', ['activo', 'en_proceso'])
            ->whereHas('maquinarias', function ($q) use ($maquinaria) {
                $q->where('maquinarias.id_maquinaria', $maquinaria->id_maquinaria);
            })
            ->first();

        if (! $lote) {
            Log::warning('Fallback clima: maquinaria sin lote activo/en_proceso', [
                'maquinaria_id' => $maquinaria->id_maquinaria,
                'fallback_fecha' => $fallbackDate->toDateString(),
            ]);

            return [
                'fecha_programada' => $fallbackDate,
                'fuente' => 'fallback',
                'motivo' => 'sin_lote_asociado',
                'lote_id' => null,
                'lluvia_mm' => null,
            ];
        }

        $clima = $this->climaDecisionService->analizarYRecomendar($lote);
        $dias = $clima['pronostico'] ?? $clima['dias_detalle'] ?? [];

        if (! ($clima['success'] ?? false) || empty($dias)) {
            Log::warning('Fallback clima: sin datos validos de pronostico', [
                'maquinaria_id' => $maquinaria->id_maquinaria,
                'lote_id' => $lote->id_lote,
                'error' => $clima['error'] ?? null,
                'fallback_fecha' => $fallbackDate->toDateString(),
            ]);

            return [
                'fecha_programada' => $fallbackDate,
                'fuente' => 'fallback',
                'motivo' => 'sin_datos_clima',
                'lote_id' => $lote->id_lote,
                'lluvia_mm' => null,
            ];
        }

        foreach ($dias as $dia) {
            $fechaRaw = $dia['fecha'] ?? null;
            $fecha = $fechaRaw instanceof Carbon ? $fechaRaw->copy() : Carbon::parse((string) $fechaRaw);
            $mm = (float) ($dia['precipitacion_mm'] ?? 0);
            $razon = mb_strtolower((string) ($dia['razon'] ?? ''));

            if ($fecha->lt(now()->startOfDay()) || $fecha->gt($limite)) {
                continue;
            }

            if ($mm >= ClimaDecisionService::UMBRAL_LLUVIA || str_contains($razon, 'lluvia')) {
                return [
                    'fecha_programada' => $fecha->startOfDay(),
                    'fuente' => 'clima',
                    'motivo' => 'lluvia_detectada',
                    'lote_id' => $lote->id_lote,
                    'lluvia_mm' => $mm,
                ];
            }
        }

        Log::warning('Fallback clima: sin lluvia en ventana de 72h', [
            'maquinaria_id' => $maquinaria->id_maquinaria,
            'lote_id' => $lote->id_lote,
            'fallback_fecha' => $fallbackDate->toDateString(),
        ]);

        return [
            'fecha_programada' => $fallbackDate,
            'fuente' => 'fallback',
            'motivo' => 'sin_lluvia_72h',
            'lote_id' => $lote->id_lote,
            'lluvia_mm' => null,
        ];
    }

    /**
     * Asigna automáticamente personal disponible a la orden generada.
     *
     * Prioriza por rol (mantenimiento -> administrativo) y luego cualquier
     * empleado activo disponible en la fecha programada.
     *
     * @return array{empleado_id: ?int, rol_origen: ?string, nombre?: string}
     */
    private function asignarPersonalAutomatico(Mantenimiento $mantenimiento, Carbon $fechaProgramada): array
    {
        $fecha = $fechaProgramada->toDateString();

        $empleado = $this->buscarEmpleadoDisponiblePorRol('mantenimiento', $fecha);
        $origen = 'mantenimiento';

        if (! $empleado) {
            $empleado = $this->buscarEmpleadoDisponiblePorRol('administrativo', $fecha);
            $origen = 'administrativo';
        }

        if (! $empleado) {
            $empleado = $this->buscarEmpleadoDisponibleSinFiltro($fecha);
            $origen = 'fallback';
        }

        if (! $empleado) {
            Log::warning('No se encontro personal disponible para mantenimiento', [
                'mantenimiento_id' => $mantenimiento->id_mantenimiento,
                'fecha_programada' => $fecha,
            ]);

            return [
                'empleado_id' => null,
                'rol_origen' => null,
            ];
        }

        $mantenimiento->empleados()->syncWithoutDetaching([
            $empleado->id_empleado => ['rol_origen' => $origen],
        ]);

        return [
            'empleado_id' => (int) $empleado->id_empleado,
            'rol_origen' => $origen,
            'nombre' => trim($empleado->apellido.', '.$empleado->nombre),
        ];
    }

    /**
     * Empleado activo y disponible en la fecha, filtrado por rol laboral.
     */
    private function buscarEmpleadoDisponiblePorRol(string $keyword, string $fecha): ?Empleado
    {
        $ocupados = $this->idsEmpleadosOcupados($fecha);

        return Empleado::query()
            ->where(function ($q) {
                $q->whereNull('fecha_fin_actividades')
                    ->orWhereDate('fecha_fin_actividades', '>', now()->toDateString());
            })
            ->whereHas('rolLaboral', function ($q) use ($keyword) {
                $q->whereLike('nombre', '%'.$keyword.'%');
            })
            ->when(! empty($ocupados), fn ($q) => $q->whereNotIn('id_empleado', $ocupados))
            ->orderBy('id_empleado')
            ->first();
    }

    /**
     * Empleado activo y disponible en la fecha, sin filtro de rol.
     */
    private function buscarEmpleadoDisponibleSinFiltro(string $fecha): ?Empleado
    {
        $ocupados = $this->idsEmpleadosOcupados($fecha);

        return Empleado::query()
            ->where(function ($q) {
                $q->whereNull('fecha_fin_actividades')
                    ->orWhereDate('fecha_fin_actividades', '>', now()->toDateString());
            })
            ->when(! empty($ocupados), fn ($q) => $q->whereNotIn('id_empleado', $ocupados))
            ->orderBy('id_empleado')
            ->first();
    }

    /**
     * @return array<int, int>
     */
    private function idsEmpleadosOcupados(string $fecha): array
    {
        return DB::table('mantenimiento_empleado as me')
            ->join('mantenimientos as m', 'm.id_mantenimiento', '=', 'me.id_mantenimiento')
            ->whereIn('m.estado', ['programado', 'en curso'])
            ->whereNull('m.deleted_at')
            ->whereDate('m.fecha_programada', $fecha)
            ->pluck('me.id_empleado')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /**
     * Crea la notificación interna de umbral alcanzado (canal de registro)
     * para los usuarios configurados.
     */
    private function crearNotificacionInterna(
        Mantenimiento $mantenimiento,
        Maquinaria $maquinaria,
        float $toneladasDesdeUltimo,
        array $programacion,
        array $asignacion
    ): void {
        try {
            $idsUsuarios = app(NotificacionService::class)
                ->cargarConfiguracionMantenimiento()['umbral'];

            if (empty($idsUsuarios)) {
                Log::warning('No hay usuarios configurados para notificacion interna de umbral.');

                return;
            }

            $fechaLimite = now()->addDays(7)->toDateString();
            $fechaProgramada = $programacion['fecha_programada']->toDateString();
            $origen = $programacion['fuente'];
            $asignado = $asignacion['nombre'] ?? 'Sin asignacion';

            $titulo = "Mantenimiento Preventivo - Maquinaria {$maquinaria->id_maquinaria}";
            $mensaje = "Se genero la orden #{$mantenimiento->id_mantenimiento}. ".
                "Toneladas detectadas: {$toneladasDesdeUltimo} (umbral {$maquinaria->umbral_toneladas}). ".
                "Fecha programada: {$fechaProgramada} (fuente {$origen}). ".
                "Personal asignado: {$asignado}.";

            foreach ($idsUsuarios as $idUsuario) {
                NotificacionSistema::create([
                    'user_id' => $idUsuario,
                    'mantenimiento_id' => $mantenimiento->id_mantenimiento,
                    'tipo' => 'umbral_alcanzado',
                    'titulo' => $titulo,
                    'mensaje' => $mensaje,
                    'fecha_limite' => $fechaLimite,
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Error en crearNotificacionInterna', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Crea la notificación interna de mantenimiento vencido (respaldo del
     * recordatorio por email) para los usuarios configurados.
     */
    private function crearNotificacionVencido(Mantenimiento $mantenimiento): void
    {
        try {
            $idsUsuarios = app(NotificacionService::class)
                ->cargarConfiguracionMantenimiento()['recordatorio'];

            if (empty($idsUsuarios)) {
                return;
            }

            $titulo = "Mantenimiento Vencido - Orden #{$mantenimiento->id_mantenimiento}";
            $mensaje = "La orden #{$mantenimiento->id_mantenimiento} programada para {$mantenimiento->fecha_programada} ".
                'no fue confirmada y quedo marcada como vencida. Requiere reprogramacion.';

            foreach ($idsUsuarios as $idUsuario) {
                NotificacionSistema::create([
                    'user_id' => $idUsuario,
                    'mantenimiento_id' => $mantenimiento->id_mantenimiento,
                    'tipo' => 'mantenimiento_vencido',
                    'titulo' => $titulo,
                    'mensaje' => $mensaje,
                    'fecha_limite' => now()->addDays(7)->toDateString(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Error creando notificacion de mantenimiento vencido', ['error' => $e->getMessage()]);
        }
    }
}
