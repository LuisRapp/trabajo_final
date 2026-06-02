<?php

namespace App\Console\Commands;

use App\Mail\MantenimientoOrdenGeneradaMail;
use App\Models\Empleado;
use App\Models\Insumo;
use App\Models\KitMantenimientoPreventivo;
use App\Models\Lote;
use App\Models\Mantenimiento;
use App\Models\MantenimientoPurchaseProposal;
use App\Models\MantenimientoPurchaseProposalInsumo;
use App\Models\Maquinaria;
use App\Models\NotificacionSistema;
use App\Models\RolLaboral;
use App\Models\TipoMantenimiento;
use App\Models\TipoMaquinaria;
use App\Models\UnidadMedida;
use App\Services\ClimaDecisionService;
use App\Services\MantenimientoDocumentsService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CheckMantenimientoUmbrales extends Command
{
    private const CLIMA_VENTANA_HORAS = 72;

    protected $signature = 'mantenimiento:check-umbrales {--maquinaria=} {--simular} {--forzar-flujo}';
    protected $description = 'Verifica umbrales, programa por clima y genera orden de mantenimiento con personal asignado';
    protected float $ultimoEnvioMail = 0.0;
    protected bool $modoForzado = false;

    public function handle(): int
    {
        $maquinariaIdOpt = $this->option('maquinaria');
        $simular = (bool) $this->option('simular');
        $forzarFlujo = (bool) $this->option('forzar-flujo');
        $this->modoForzado = $forzarFlujo;

        if ($maquinariaIdOpt && $simular) {
            $this->forzarUmbralParaSimulacion((int) $maquinariaIdOpt);
        }

        $this->info('Iniciando verificacion automatica de umbrales de mantenimiento...');

        $maquinarias = Maquinaria::query()
            ->with('tipoMaquinaria')
            ->whereNotNull('umbral_toneladas')
            ->whereIn('estado', ['operativa', 'activo'])
            ->when($maquinariaIdOpt, fn ($q) => $q->where('id_maquinaria', (int) $maquinariaIdOpt))
            ->get();

        if ($forzarFlujo) {
            $maquinariaForzada = $this->prepararEscenarioForzado($maquinariaIdOpt ? (int) $maquinariaIdOpt : null);
            if ($maquinariaForzada) {
                $maquinarias = collect([$maquinariaForzada]);
                $this->info("Modo forzado activo. Maquinaria objetivo: {$maquinariaForzada->id_maquinaria}");
            }
        }

        if ($maquinarias->isEmpty()) {
            $this->warn('No hay maquinarias elegibles para verificar.');
            return self::SUCCESS;
        }

        $tipoPreventivo = $this->obtenerTipoPreventivo($forzarFlujo);
        if (!$tipoPreventivo) {
            $this->error('No existe tipo de mantenimiento preventivo configurado.');
            return self::SUCCESS;
        }

        $ordenesGeneradas = 0;
        $advertenciasStock = [];

        foreach ($maquinarias as $maquinaria) {
            $toneladasDesdeUltimo = $this->obtenerToneladasDesdeUltimoMantenimiento($maquinaria);
            $umbral = (float) $maquinaria->umbral_toneladas;

            if ($forzarFlujo && $toneladasDesdeUltimo < $umbral) {
                $this->forzarUmbralParaSimulacion((int) $maquinaria->id_maquinaria);
                $maquinaria->refresh();
                $toneladasDesdeUltimo = $this->obtenerToneladasDesdeUltimoMantenimiento($maquinaria);
            }

            if ($toneladasDesdeUltimo < $umbral) {
                continue;
            }

            $ordenPendiente = Mantenimiento::query()
                ->where('id_maquinaria', $maquinaria->id_maquinaria)
                ->whereIn('estado', ['programado', 'en curso'])
                ->exists();

            if ($ordenPendiente) {
                $this->warn("Maquinaria {$maquinaria->id_maquinaria}: ya existe orden abierta.");
                continue;
            }

            $programacion = $this->resolverFechaProgramadaPorClima($maquinaria);
            $faltaStock = false;
            $insumosConProblema = [];
            $mantenimiento = null;
            $proposal = null;

            DB::beginTransaction();
            try {
                $mantenimiento = Mantenimiento::create([
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

                [$faltaStock, $insumosConProblema] = $this->detectarFaltantesKit($maquinaria);

                if ($faltaStock) {
                    $proposal = $this->crearPropuestaCompraMantenimiento($mantenimiento, $insumosConProblema);
                }

                $this->crearNotificacionInterna(
                    mantenimiento: $mantenimiento,
                    maquinaria: $maquinaria,
                    toneladasDesdeUltimo: $toneladasDesdeUltimo,
                    programacion: $programacion,
                    asignacion: $asignacion
                );

                DB::commit();
            } catch (\Throwable $e) {
                DB::rollBack();
                $this->error("Error creando orden para maquinaria {$maquinaria->id_maquinaria}: {$e->getMessage()}");
                Log::error('Error en mantenimiento:check-umbrales', [
                    'maquinaria_id' => $maquinaria->id_maquinaria,
                    'error' => $e->getMessage(),
                ]);
                continue;
            }

            $this->enviarCorreoOrdenConAdjuntos($mantenimiento, $proposal);

            $ordenesGeneradas++;
            $this->info(
                "Orden #{$mantenimiento->id_mantenimiento} creada para maquinaria {$maquinaria->id_maquinaria}. " .
                "Fecha: {$programacion['fecha_programada']->toDateString()} ({$programacion['fuente']})."
            );

            if ($faltaStock) {
                $advertenciasStock[] = [
                    'maquinaria' => $maquinaria->id_maquinaria,
                    'orden' => $mantenimiento->id_mantenimiento,
                    'insumos' => $insumosConProblema,
                ];
                $this->warn("Orden #{$mantenimiento->id_mantenimiento}: faltan insumos para kit preventivo.");
            }
        }

        $this->info('');
        $this->info('=== RESUMEN ===');
        $this->info("Ordenes generadas: {$ordenesGeneradas}");

        if (!empty($advertenciasStock)) {
            $this->warn('Advertencias de stock:');
            foreach ($advertenciasStock as $adv) {
                $this->warn("Orden #{$adv['orden']} - Maquinaria {$adv['maquinaria']}");
                foreach ($adv['insumos'] as $ins) {
                    $this->warn("  - {$ins['insumo']}: faltan {$ins['faltante']} unidades");
                }
            }
            Log::warning('Ordenes creadas con faltantes de stock', ['advertencias' => $advertenciasStock]);
        }

        $this->info('Verificacion completada.');
        return self::SUCCESS;
    }

    private function obtenerTipoPreventivo(bool $forzarFlujo): ?TipoMantenimiento
    {
        $tipo = TipoMantenimiento::query()
            ->where('nombre', 'ILIKE', '%preventivo%')
            ->where('activo', true)
            ->first();

        if ($tipo || !$forzarFlujo) {
            return $tipo;
        }

        $tipo = TipoMantenimiento::create([
            'nombre' => 'Preventivo',
            'activo' => true,
        ]);

        $this->info("Modo forzado: tipo de mantenimiento creado (#{$tipo->id_tipo_mantenimiento}).");

        return $tipo;
    }

    private function prepararEscenarioForzado(?int $maquinariaId = null): ?Maquinaria
    {
        try {
            $maquinaria = null;

            if ($maquinariaId) {
                $maquinaria = Maquinaria::query()
                    ->with('tipoMaquinaria')
                    ->find($maquinariaId);

                if (!$maquinaria) {
                    $this->warn("Modo forzado: no existe la maquinaria {$maquinariaId}, se creara una de prueba.");
                }
            }

            if (!$maquinaria) {
                $maquinaria = $this->crearMaquinariaDePruebaParaFlujoForzado();
            }

            if (!$maquinaria) {
                return null;
            }

            if (!in_array((string) $maquinaria->estado, ['operativa', 'activo'], true)) {
                $maquinaria->estado = 'operativa';
            }

            $umbral = (float) ($maquinaria->umbral_toneladas ?? 0);
            if ($umbral <= 0) {
                $umbral = 50.0;
                $maquinaria->umbral_toneladas = $umbral;
            }

            if ((float) $maquinaria->toneladas_acumuladas < $umbral) {
                $maquinaria->toneladas_acumuladas = $umbral + 5;
            }

            if (!$maquinaria->fecha_inicio_actividades) {
                $maquinaria->fecha_inicio_actividades = now()->subDays(15)->toDateString();
            }

            $maquinaria->save();

            $this->obtenerTipoPreventivo(true);
            $this->asegurarPersonalParaFlujoForzado();
            $this->asegurarKitConFaltanteParaFlujoForzado($maquinaria);

            return $maquinaria->fresh(['tipoMaquinaria']);
        } catch (\Throwable $e) {
            $this->error("No se pudo preparar el escenario forzado: {$e->getMessage()}");
            Log::error('Error preparando escenario forzado en mantenimiento:check-umbrales', [
                'maquinaria_id' => $maquinariaId,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    private function crearMaquinariaDePruebaParaFlujoForzado(): ?Maquinaria
    {
        $tipo = TipoMaquinaria::query()
            ->where('nombre', 'ILIKE', '%demo mantenimiento%')
            ->first();

        if (!$tipo) {
            $tipo = TipoMaquinaria::create([
                'nombre' => 'Demo mantenimiento',
                'umbral_toneladas' => 50,
            ]);
        } elseif ((float) ($tipo->umbral_toneladas ?? 0) <= 0) {
            $tipo->umbral_toneladas = 50;
            $tipo->save();
        }

        $codigo = now()->format('YmdHis');

        return Maquinaria::create([
            'id_tipo_maquinaria' => $tipo->id_tipo_maquinaria,
            'modelo' => "FORZADA-{$codigo}",
            'estado' => 'operativa',
            'es_alquilada' => false,
            'fecha_inicio_actividades' => now()->subDays(30)->toDateString(),
            'toneladas_acumuladas' => 55,
            'umbral_toneladas' => 50,
        ]);
    }

    private function asegurarPersonalParaFlujoForzado(): void
    {
        $activosBase = Empleado::query()
            ->where(function ($q) {
                $q->whereNull('fecha_fin_actividades')
                    ->orWhereDate('fecha_fin_actividades', '>', now()->toDateString());
            });

        $tieneMantenimiento = (clone $activosBase)
            ->whereHas('rolLaboral', function ($q) {
                $q->where('activo', true)
                    ->where('nombre', 'ILIKE', '%mantenimiento%');
            })
            ->exists();

        $tieneAdministrativo = (clone $activosBase)
            ->whereHas('rolLaboral', function ($q) {
                $q->where('activo', true)
                    ->where('nombre', 'ILIKE', '%administrativo%');
            })
            ->exists();

        if ($tieneMantenimiento || $tieneAdministrativo) {
            return;
        }

        $rolAdmin = RolLaboral::query()
            ->where('activo', true)
            ->where('nombre', 'ILIKE', '%administrativo%')
            ->first();

        if (!$rolAdmin) {
            $rolAdmin = RolLaboral::create([
                'nombre' => 'Personal administrativo',
                'costo_diario' => 0,
                'activo' => true,
            ]);
        }

        $dni = $this->generarDniUnicoParaPruebas();
        Empleado::create([
            'id_rol_laboral' => $rolAdmin->id_rol_laboral,
            'dni' => $dni,
            'apellido' => 'Forzado',
            'nombre' => 'Administrativo',
            'email' => "forzado.{$dni}@example.com",
            'fecha_inicio_actividades' => now()->toDateString(),
        ]);

        $this->info('Modo forzado: se creo personal administrativo de respaldo para asignacion.');
    }

    private function asegurarKitConFaltanteParaFlujoForzado(Maquinaria $maquinaria): void
    {
        $unidad = UnidadMedida::query()
            ->where('abreviatura', 'ud')
            ->first();

        if (!$unidad) {
            $unidad = UnidadMedida::create([
                'nombre' => 'Unidad',
                'abreviatura' => 'ud',
            ]);
        }

        $insumo = Insumo::query()
            ->where('nombre', 'ILIKE', 'Insumo demo mantenimiento')
            ->first();

        if (!$insumo) {
            $insumo = Insumo::create([
                'nombre' => 'Insumo demo mantenimiento',
                'descripcion' => 'Generado para flujo forzado de mantenimiento',
                'id_unidad_medida' => $unidad->id_unidad_medida,
            ]);
        } elseif ((int) $insumo->id_unidad_medida !== (int) $unidad->id_unidad_medida) {
            $insumo->id_unidad_medida = $unidad->id_unidad_medida;
            $insumo->save();
        }

        $stockDisponible = (float) DB::table('movimiento_stocks')
            ->where('id_insumo', $insumo->id_insumo)
            ->selectRaw(
                "COALESCE(SUM(CASE WHEN tipo = 'entrada' THEN cantidad WHEN tipo = 'salida' THEN -cantidad ELSE 0 END), 0) as stock"
            )
            ->value('stock');

        $cantidadRequerida = max($stockDisponible + 10, 10);

        $kit = KitMantenimientoPreventivo::query()
            ->withTrashed()
            ->where('id_maquinaria', $maquinaria->id_maquinaria)
            ->where('id_insumo', $insumo->id_insumo)
            ->first();

        if (!$kit) {
            $kit = new KitMantenimientoPreventivo();
            $kit->id_maquinaria = $maquinaria->id_maquinaria;
            $kit->id_insumo = $insumo->id_insumo;
        } elseif ($kit->trashed()) {
            $kit->restore();
        }

        $kit->id_tipo_maquinaria = $maquinaria->id_tipo_maquinaria;
        $kit->cantidad_requerida = $cantidadRequerida;
        $kit->es_obligatorio = true;
        $kit->save();
    }

    private function generarDniUnicoParaPruebas(): string
    {
        do {
            $dni = (string) random_int(30000000, 49999999);
        } while (Empleado::query()->where('dni', $dni)->exists());

        return $dni;
    }

    private function forzarUmbralParaSimulacion(int $maquinariaId): void
    {
        try {
            $maq = Maquinaria::find($maquinariaId);
            if (!$maq) {
                $this->warn("No se encontro maquinaria {$maquinariaId} para simulacion.");
                return;
            }

            if (!$maq->umbral_toneladas) {
                $this->warn("Maquinaria {$maquinariaId} no tiene umbral configurado.");
                return;
            }

            if ($maq->toneladas_acumuladas < $maq->umbral_toneladas) {
                $maq->toneladas_acumuladas = $maq->umbral_toneladas + 5;
                $maq->save();
                $this->info(
                    "Simulacion: maquinaria {$maq->id_maquinaria} supera umbral " .
                    "({$maq->toneladas_acumuladas}/{$maq->umbral_toneladas})"
                );
            }
        } catch (\Throwable $e) {
            $this->warn("No se pudo simular umbral: {$e->getMessage()}");
        }
    }

    private function obtenerToneladasDesdeUltimoMantenimiento(Maquinaria $maquinaria): float
    {
        $ultimoMantenimiento = Mantenimiento::query()
            ->where('id_maquinaria', $maquinaria->id_maquinaria)
            ->whereNotNull('toneladas_snapshot')
            ->orderBy('fecha_fin', 'desc')
            ->first();

        if (!$ultimoMantenimiento) {
            return (float) $maquinaria->toneladas_acumuladas;
        }

        return (float) $maquinaria->toneladas_acumuladas - (float) $ultimoMantenimiento->toneladas_snapshot;
    }

    /**
     * Regla:
     * - Si hay lluvia dentro de 72h -> usar ese dia exacto.
     * - Si no hay lluvia o faltan datos -> fallback al dia siguiente.
     */
    private function resolverFechaProgramadaPorClima(Maquinaria $maquinaria): array
    {
        $fallbackDate = now()->addDay()->startOfDay();
        $limite = now()->addHours(self::CLIMA_VENTANA_HORAS);

        $lote = Lote::query()
            ->whereIn('estado', ['activo', 'en_proceso'])
            ->whereExists(function ($q) use ($maquinaria) {
                $q->select(DB::raw(1))
                    ->from('lote_maquinaria as lm')
                    ->whereColumn('lm.id_lote', 'lotes.id_lote')
                    ->where('lm.id_maquinaria', $maquinaria->id_maquinaria);
            })
            ->first();

        if (!$lote) {
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

        $clima = app(ClimaDecisionService::class)->analizarYRecomendar($lote);
        $dias = $clima['pronostico'] ?? $clima['dias_detalle'] ?? [];

        if (!($clima['success'] ?? false) || empty($dias)) {
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

    private function detectarFaltantesKit(Maquinaria $maquinaria): array
    {
        $kit = DB::table('kit_mantenimiento_preventivo as k')
            ->leftJoin('insumos as i', 'i.id_insumo', '=', 'k.id_insumo')
            ->where('k.id_maquinaria', $maquinaria->id_maquinaria)
            ->whereNull('k.deleted_at')
            ->select([
                'k.id_insumo',
                'k.cantidad_requerida',
                'i.nombre as insumo_nombre',
            ])
            ->get();

        if ($kit->isEmpty()) {
            $kit = DB::table('kit_mantenimiento_preventivo as k')
                ->leftJoin('insumos as i', 'i.id_insumo', '=', 'k.id_insumo')
                ->where('k.id_tipo_maquinaria', $maquinaria->id_tipo_maquinaria)
                ->whereNull('k.deleted_at')
                ->select([
                    'k.id_insumo',
                    'k.cantidad_requerida',
                    'i.nombre as insumo_nombre',
                ])
                ->get();
        }

        $insumosConProblema = [];

        foreach ($kit as $item) {
            $stockDisponible = (float) DB::table('movimiento_stocks')
                ->where('id_insumo', $item->id_insumo)
                ->selectRaw(
                    "COALESCE(SUM(CASE WHEN tipo = 'entrada' THEN cantidad WHEN tipo = 'salida' THEN -cantidad ELSE 0 END), 0) as stock"
                )
                ->value('stock');

            if ($stockDisponible < (float) $item->cantidad_requerida) {
                $insumosConProblema[] = [
                    'id_insumo' => (int) $item->id_insumo,
                    'insumo' => (string) ($item->insumo_nombre ?? 'Insumo'),
                    'requerido' => (float) $item->cantidad_requerida,
                    'disponible' => $stockDisponible,
                    'faltante' => (float) $item->cantidad_requerida - $stockDisponible,
                ];
            }
        }

        return [!empty($insumosConProblema), $insumosConProblema];
    }

    private function asignarPersonalAutomatico(Mantenimiento $mantenimiento, Carbon $fechaProgramada): array
    {
        $fecha = $fechaProgramada->toDateString();

        $empleado = $this->buscarEmpleadoDisponiblePorRol('mantenimiento', $fecha);
        $origen = 'mantenimiento';

        if (!$empleado) {
            $empleado = $this->buscarEmpleadoDisponiblePorRol('administrativo', $fecha);
            $origen = 'administrativo';
        }

        if (!$empleado) {
            if ($this->modoForzado) {
                $empleado = $this->crearEmpleadoAdministrativoFallback();
                $origen = 'administrativo_creado';
            }
        }

        if (!$empleado) {
            $empleado = $this->buscarEmpleadoDisponibleSinFiltro($fecha);
            $origen = 'fallback';
        }

        if (!$empleado) {
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
            'nombre' => trim($empleado->apellido . ', ' . $empleado->nombre),
        ];
    }

    private function crearEmpleadoAdministrativoFallback(): ?Empleado
    {
        try {
            $rolAdmin = RolLaboral::query()
                ->where('activo', true)
                ->where('nombre', 'ILIKE', '%administrativo%')
                ->first();

            if (!$rolAdmin) {
                $rolAdmin = RolLaboral::create([
                    'nombre' => 'Personal administrativo',
                    'costo_diario' => 0,
                    'activo' => true,
                ]);
            }

            $dni = $this->generarDniUnicoParaPruebas();

            return Empleado::create([
                'id_rol_laboral' => $rolAdmin->id_rol_laboral,
                'dni' => $dni,
                'apellido' => 'Auto',
                'nombre' => 'Administrativo',
                'email' => "autogen.{$dni}@example.com",
                'fecha_inicio_actividades' => now()->toDateString(),
            ]);
        } catch (\Throwable $e) {
            Log::error('No se pudo crear empleado administrativo fallback', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    private function buscarEmpleadoDisponiblePorRol(string $keyword, string $fecha): ?Empleado
    {
        $ocupados = DB::table('mantenimiento_empleado as me')
            ->join('mantenimientos as m', 'm.id_mantenimiento', '=', 'me.id_mantenimiento')
            ->whereIn('m.estado', ['programado', 'en curso'])
            ->whereDate('m.fecha_programada', $fecha)
            ->pluck('me.id_empleado')
            ->map(fn ($id) => (int) $id)
            ->all();

        return Empleado::query()
            ->where(function ($q) {
                $q->whereNull('fecha_fin_actividades')
                    ->orWhereDate('fecha_fin_actividades', '>', now()->toDateString());
            })
            ->whereHas('rolLaboral', function ($q) use ($keyword) {
                $q->where('activo', true)
                    ->where('nombre', 'ILIKE', '%' . $keyword . '%');
            })
            ->when(!empty($ocupados), fn ($q) => $q->whereNotIn('id_empleado', $ocupados))
            ->orderBy('id_empleado')
            ->first();
    }

    private function buscarEmpleadoDisponibleSinFiltro(string $fecha): ?Empleado
    {
        $ocupados = DB::table('mantenimiento_empleado as me')
            ->join('mantenimientos as m', 'm.id_mantenimiento', '=', 'me.id_mantenimiento')
            ->whereIn('m.estado', ['programado', 'en curso'])
            ->whereDate('m.fecha_programada', $fecha)
            ->pluck('me.id_empleado')
            ->map(fn ($id) => (int) $id)
            ->all();

        return Empleado::query()
            ->where(function ($q) {
                $q->whereNull('fecha_fin_actividades')
                    ->orWhereDate('fecha_fin_actividades', '>', now()->toDateString());
            })
            ->when(!empty($ocupados), fn ($q) => $q->whereNotIn('id_empleado', $ocupados))
            ->orderBy('id_empleado')
            ->first();
    }

    private function crearPropuestaCompraMantenimiento(
        Mantenimiento $mantenimiento,
        array $insumosConProblema
    ): MantenimientoPurchaseProposal {
        $proposal = MantenimientoPurchaseProposal::firstOrCreate(
            ['id_mantenimiento' => $mantenimiento->id_mantenimiento],
            [
                'id_maquinaria' => $mantenimiento->id_maquinaria,
                'status' => 'pending',
            ]
        );

        if ($proposal->id_maquinaria !== $mantenimiento->id_maquinaria) {
            $proposal->id_maquinaria = $mantenimiento->id_maquinaria;
            $proposal->save();
        }

        MantenimientoPurchaseProposalInsumo::query()
            ->where('id_mantenimiento_purchase_proposal', $proposal->id_mantenimiento_purchase_proposal)
            ->delete();

        foreach ($insumosConProblema as $ins) {
            if (empty($ins['id_insumo'])) {
                continue;
            }
            MantenimientoPurchaseProposalInsumo::create([
                'id_mantenimiento_purchase_proposal' => $proposal->id_mantenimiento_purchase_proposal,
                'id_insumo' => (int) $ins['id_insumo'],
                'cantidad_requerida' => (float) ($ins['requerido'] ?? 0),
                'stock_disponible' => (float) ($ins['disponible'] ?? 0),
                'faltante' => (float) ($ins['faltante'] ?? 0),
            ]);
        }

        return $proposal->fresh(['insumos.insumo.unidadMedida', 'maquinaria.tipoMaquinaria', 'mantenimiento']);
    }

    private function enviarCorreoOrdenConAdjuntos(
        Mantenimiento $mantenimiento,
        ?MantenimientoPurchaseProposal $proposal
    ): void {
        $emails = $this->resolveMailRecipients();
        if (empty($emails)) {
            return;
        }

        try {
            $mantenimiento->loadMissing(['maquinaria.tipoMaquinaria', 'tipoMantenimiento', 'empleados.rolLaboral']);

            /** @var MantenimientoDocumentsService $documents */
            $documents = app(MantenimientoDocumentsService::class);
            $attachments = [];

            $attachments[] = $documents->generateMaintenanceOrderPdf($mantenimiento);

            if ($proposal) {
                $attachments[] = $documents->generatePurchaseOrderPdf($proposal);
            }

            $this->enviarConReintento(function () use ($emails, $mantenimiento, $proposal, $attachments) {
                $this->esperarParaEnviarMail();
                Mail::to($emails)->send(new MantenimientoOrdenGeneradaMail($mantenimiento, $proposal, $attachments));
            });

            if ($proposal) {
                $meta = is_array($proposal->meta) ? $proposal->meta : [];
                $meta['purchase_order'] = [
                    'sent_at' => now()->toISOString(),
                    'recipients' => $emails,
                    'attachments' => array_map(fn ($a) => $a['path'] ?? null, $attachments),
                ];
                $proposal->meta = $meta;
                $proposal->status = 'sent';
                $proposal->save();
            }
        } catch (\Throwable $e) {
            $this->warn("No se pudo enviar mail con adjuntos: {$e->getMessage()}");
            Log::error('Error enviando mail de mantenimiento con adjuntos', [
                'mantenimiento_id' => $mantenimiento->id_mantenimiento,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function resolveMailRecipients(): array
    {
        $emails = array_values(array_filter((array) config('mail.purchase_order_emails', [])));
        $admin = trim((string) config('mail.admin_email', ''));
        if ($admin !== '') {
            $emails[] = $admin;
        }

        return array_values(array_unique(array_filter($emails)));
    }

    protected function enviarConReintento(callable $enviar): void
    {
        $intentos = 0;
        $maxIntentos = 3;
        $espera = 2;

        while (true) {
            try {
                $enviar();
                return;
            } catch (\Exception $e) {
                $intentos++;
                $mensaje = $e->getMessage();
                $esRateLimit = stripos($mensaje, 'Too many emails per second') !== false || stripos($mensaje, '550') !== false;
                if (!$esRateLimit || $intentos >= $maxIntentos) {
                    throw $e;
                }
                sleep($espera);
                $espera *= 2;
            }
        }
    }

    protected function esperarParaEnviarMail(): void
    {
        $minInterval = 1.5;
        $ahora = microtime(true);
        $ultimoGlobal = cache()->get('mantenimiento_mail_last_sent_at');
        $referencia = max((float) $this->ultimoEnvioMail, (float) $ultimoGlobal);

        if ($referencia > 0) {
            $delta = $ahora - $referencia;
            if ($delta < $minInterval) {
                usleep((int) (($minInterval - $delta) * 1000000));
            }
        }

        $this->ultimoEnvioMail = microtime(true);
        cache()->put('mantenimiento_mail_last_sent_at', $this->ultimoEnvioMail, 60);
    }

    /**
     * Crea notificacion interna del sistema para usuarios configurados.
     */
    protected function crearNotificacionInterna(
        Mantenimiento $mantenimiento,
        Maquinaria $maquinaria,
        float $toneladasDesdeUltimo,
        array $programacion,
        array $asignacion
    ): void {
        try {
            $userIds = DB::table('configuracion_notificaciones_mantenimiento')
                ->where('tipo_notificacion', 'umbral')
                ->pluck('user_id');

            if ($userIds->isEmpty()) {
                $this->warn('No hay usuarios configurados para notificacion interna de umbral.');
                return;
            }

            $fechaLimite = now()->addDays(7)->toDateString();
            $fechaProgramada = $programacion['fecha_programada']->toDateString();
            $origen = $programacion['fuente'];
            $asignado = $asignacion['nombre'] ?? 'Sin asignacion';

            $titulo = "Mantenimiento Preventivo - Maquinaria {$maquinaria->id_maquinaria}";
            $mensaje = "Se genero la orden #{$mantenimiento->id_mantenimiento}. " .
                "Toneladas detectadas: {$toneladasDesdeUltimo} (umbral {$maquinaria->umbral_toneladas}). " .
                "Fecha programada: {$fechaProgramada} (fuente {$origen}). " .
                "Personal asignado: {$asignado}.";

            foreach ($userIds as $userId) {
                NotificacionSistema::create([
                    'user_id' => $userId,
                    'mantenimiento_id' => $mantenimiento->id_mantenimiento,
                    'tipo' => 'umbral_alcanzado',
                    'titulo' => $titulo,
                    'mensaje' => $mensaje,
                    'fecha_limite' => $fechaLimite,
                ]);
            }
        } catch (\Throwable $e) {
            $this->warn("Error creando notificacion interna: {$e->getMessage()}");
            Log::error('Error en crearNotificacionInterna', ['error' => $e->getMessage()]);
        }
    }
}
