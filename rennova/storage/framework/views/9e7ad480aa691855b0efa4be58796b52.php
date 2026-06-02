<?php
    $lotes = \App\Models\Lote::query()->orderByDesc('created_at')->get();
    $loteSeleccionadoId = (int) request('lote');
    $lote = null;
    if ($loteSeleccionadoId) {
        $lote = \App\Models\Lote::find($loteSeleccionadoId);
    }
    if (!$lote) {
        $lote = $lotes->first();
    }
    $svc = app(\App\Services\ClimaDecisionService::class);
    $data = $lote ? $svc->analizarYRecomendar($lote) : null;
    
    // Extraer datos
    $datosCalc = $data['datos_calculados'] ?? [];
    $diasPerdidos = $datosCalc['dias_perdidos'] ?? $data['total_dias_perdidos'] ?? 0;
    $deficitTn = $datosCalc['volumen_riesgo'] ?? $data['deficit_tn'] ?? 0;
    $accionPct = $datosCalc['aumento_necesario_pct'] ?? $data['accion_porcentaje'] ?? 0;
    $estrategia = $data['estrategia'] ?? 'NORMAL';
    $nivelUrgencia = $data['nivel_urgencia'] ?? 'BAJA';
    $pronostico = $data['pronostico'] ?? $data['dias_detalle'] ?? [];
?>

<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => __('Dashboard')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Dashboard'))]); ?>
    <!-- Estilos inline para sobrescribir flux:main -->
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Fraunces:wght@500;700&family=IBM+Plex+Sans:wght@400;500;600&display=swap');

        flux\:main, [data-flux-main], .flux-main {
            max-width: none !important;
            width: 100% !important;
        }

        :root {
            --clima-ink: #0f172a;
            --clima-muted: #475569;
            --clima-ash: #94a3b8;
            --clima-forest: #0f4f3f;
            --clima-sun: #f6c35c;
            --clima-mist: #eef2f7;
        }

        .clima-shell {
            background:
                radial-gradient(1200px 320px at 12% -20%, rgba(15, 79, 63, 0.12), transparent),
                radial-gradient(1000px 400px at 100% 0%, rgba(246, 195, 92, 0.18), transparent),
                #f8fafc;
            min-height: calc(100vh - 2rem);
            border-radius: 28px;
            padding: 1.5rem;
        }

        .clima-title {
            font-family: "Fraunces", serif;
            letter-spacing: -0.02em;
            color: var(--clima-ink);
        }

        .clima-body {
            font-family: "IBM Plex Sans", sans-serif;
            color: var(--clima-muted);
        }

        .clima-chip {
            border-radius: 999px;
            padding: 0.25rem 0.75rem;
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.02em;
        }

        .clima-card {
            border-radius: 22px;
            background: #fff;
            border: 1px solid #e2e8f0;
            box-shadow: 0 14px 40px rgba(15, 23, 42, 0.06);
        }

        .clima-kpi {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            border: 1px solid #e2e8f0;
        }

        .clima-kpi-number {
            font-family: "Fraunces", serif;
            color: var(--clima-ink);
        }

        .clima-status-normal { background: rgba(15, 79, 63, 0.12); color: var(--clima-forest); }
        .clima-status-media { background: rgba(246, 195, 92, 0.2); color: #a16207; }
        .clima-status-alta { background: rgba(220, 38, 38, 0.15); color: #b91c1c; }

        .clima-day {
            background: #fff;
            border-radius: 18px;
            border: 1px solid #e2e8f0;
            padding: 1rem;
        }

        .clima-day-bar {
            height: 6px;
            border-radius: 999px;
        }
    </style>
    
    <!-- Container Principal Moderno con ancho completo -->
    <div class="!max-w-none w-full clima-shell">
        
        <!-- Header Moderno -->
        <div class="mb-8">
            <div class="clima-card p-6 sm:p-8">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                    <!-- Título -->
                    <div>
                        <h1 class="text-3xl sm:text-4xl font-bold clima-title">Radar Climático Operativo</h1>
                        <p class="clima-body mt-2 text-sm">Decisiones inteligentes para el lote seleccionado.</p>
                        <div class="flex flex-wrap items-center gap-2 mt-4">
                            <span class="clima-chip <?php echo e($nivelUrgencia === 'ALTA' || $nivelUrgencia === 'CRITICA' ? 'clima-status-alta' : ($nivelUrgencia === 'MEDIA' ? 'clima-status-media' : 'clima-status-normal')); ?>">
                                <?php echo e(ucfirst(strtolower($nivelUrgencia))); ?>

                            </span>
                            <span class="clima-chip bg-slate-100 text-slate-600">Estrategia: <?php echo e(str_replace('_', ' ', $estrategia)); ?></span>
                            <span class="clima-chip bg-emerald-50 text-emerald-700">Ventana: <?php echo e(count($pronostico)); ?> días</span>
                        </div>
                    </div>
                    
                    <!-- Controles -->
                    <form method="GET" action="<?php echo e(route('dashboard')); ?>" class="flex flex-col sm:flex-row gap-3">
                        <!-- Selector de Lotes -->
                        <div class="relative">
                            <select name="lote" class="appearance-none bg-white border border-gray-200 rounded-xl px-4 py-3 pr-10 text-sm font-medium text-gray-700 hover:border-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-700 focus:border-transparent transition-all duration-200 cursor-pointer">
                                <?php if($lotes->count() > 0): ?>
                                    <?php $__currentLoopData = $lotes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $op): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($op->id_lote); ?>" <?php if(optional($lote)->id_lote === $op->id_lote): echo 'selected'; endif; ?>>
                                            <?php echo e($op->propietario ?? $op->nombre ?? ('Lote #' . $op->id_lote)); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <?php else: ?>
                                    <option>No hay lotes disponibles</option>
                                <?php endif; ?>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </div>
                        </div>
                        
                        <!-- Botón Actualizar -->
                        <button type="submit" class="bg-emerald-700 hover:bg-emerald-800 text-white font-medium px-6 py-3 rounded-xl shadow-sm hover:shadow-md transition-all duration-200 flex items-center justify-center gap-2 active:scale-95">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            <span>Actualizar</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <?php if($data): ?>
            <!-- Contenido Principal -->
            <div class="space-y-6">
                
                <!-- KPIs Section -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    
                    <!-- Días Perdidos -->
                    <div class="clima-card clima-kpi p-6 sm:p-7">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Días Perdidos</p>
                                <p class="text-5xl font-bold mt-3 clima-kpi-number"><?php echo e($diasPerdidos); ?></p>
                            </div>
                            <div class="bg-rose-50 p-3 rounded-2xl">
                                <svg class="w-6 h-6 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="mt-6 pt-5 border-t border-slate-100">
                            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Período</p>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-slate-700"><?php echo e($data['rango_perdidos'] ?? 'Últimos 7 días'); ?></span>
                                <?php if($diasPerdidos > 5): ?>
                                    <span class="clima-chip clima-status-alta">Alto</span>
                                <?php elseif($diasPerdidos > 2): ?>
                                    <span class="clima-chip clima-status-media">Moderado</span>
                                <?php else: ?>
                                    <span class="clima-chip clima-status-normal">Normal</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Déficit TN -->
                    <div class="clima-card clima-kpi p-6 sm:p-7">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Déficit TN</p>
                                <p class="text-5xl font-bold mt-3 clima-kpi-number"><?php echo e(round((float) $deficitTn, 1)); ?></p>
                            </div>
                            <div class="bg-amber-50 p-3 rounded-2xl">
                                <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/>
                                </svg>
                            </div>
                        </div>
                        <div class="mt-6 pt-5 border-t border-slate-100">
                            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Acción Recomendada</p>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-slate-700">Ajuste operativo</span>
                                <span class="text-sm font-semibold text-amber-700">+<?php echo e(round((float) $accionPct, 0)); ?>%</span>
                            </div>
                        </div>
                    </div>

                    <!-- Resumen estrategia -->
                    <div class="clima-card p-6 sm:p-7">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Recomendación</p>
                                <p class="text-xl font-semibold text-slate-900 mt-2"><?php echo e(str_replace('_', ' ', $estrategia)); ?></p>
                                <p class="text-sm text-slate-500 mt-2"><?php echo e($data['recomendacion'] ?? 'Sin recomendaciones disponibles.'); ?></p>
                            </div>
                            <div class="bg-emerald-50 p-3 rounded-2xl">
                                <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Widget de Clima -->
                <div class="clima-card p-6 sm:p-8">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-6">
                        <div>
                            <h2 class="text-2xl font-bold clima-title">Pronóstico de Operatividad</h2>
                            <p class="text-sm text-slate-500 mt-1">Lectura rápida de ventanas operativas y restricciones.</p>
                        </div>
                        <span class="clima-chip bg-slate-100 text-slate-600">Próximos <?php echo e(count($pronostico)); ?> días</span>
                    </div>
                    
                    <!-- Cinta de días -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-7 gap-4">
                        <?php $__currentLoopData = $pronostico; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dia): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $estadoDia = strtoupper($dia['estado'] ?? 'OPERATIVO');
                                $esOperativo = $estadoDia === 'OPERATIVO';
                                $esCondicional = $estadoDia === 'OPERATIVO_CONDICIONAL';
                                $razon = $dia['razon'] ?? 'Normal';
                                $diaCorto = mb_substr($dia['dia_semana'] ?? 'Día', 0, 3);
                                $fecha = $dia['fecha_str'] ?? '';
                                $lluviaDiurna = $dia['lluvia_diurna_mm'] ?? null;
                                $lluviaMadrugada = $dia['lluvia_madrugada_mm'] ?? null;
                                $isWeekend = isset($dia['razon']) && stripos((string) $dia['razon'], 'fin de semana') !== false;
                            ?>
                            <div class="clima-day">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-semibold text-slate-900"><?php echo e(ucfirst($diaCorto)); ?></p>
                                        <p class="text-xs text-slate-500"><?php echo e($fecha); ?></p>
                                    </div>
                                    <span class="clima-chip <?php echo e($esOperativo ? 'clima-status-normal' : ($esCondicional ? 'clima-status-media' : 'clima-status-alta')); ?>">
                                        <?php echo e($esOperativo ? 'Operativo' : ($esCondicional ? 'Condicional' : ($isWeekend ? 'No laboral' : 'Inactivo'))); ?>

                                    </span>
                                </div>

                                <div class="mt-4 flex items-center gap-3">
                                    <div class="<?php echo e($esOperativo ? 'bg-emerald-50' : ($esCondicional ? 'bg-amber-50' : 'bg-rose-50')); ?> p-3 rounded-2xl">
                                        <?php if($esOperativo): ?>
                                            <svg class="w-7 h-7 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                                            </svg>
                                        <?php elseif($esCondicional): ?>
                                            <svg class="w-7 h-7 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h10a4 4 0 000-8 5 5 0 10-9 4"/>
                                            </svg>
                                        <?php else: ?>
                                            <svg class="w-7 h-7 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                            </svg>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-xs text-slate-500">
                                        <?php if($lluviaMadrugada !== null): ?>
                                            <div>Madrugada: <?php echo e($lluviaMadrugada); ?> mm</div>
                                        <?php endif; ?>
                                        <?php if($lluviaDiurna !== null): ?>
                                            <div>Diurna: <?php echo e($lluviaDiurna); ?> mm</div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <div class="clima-day-bar <?php echo e($esOperativo ? 'bg-emerald-500' : ($esCondicional ? 'bg-amber-400' : 'bg-rose-500')); ?>"></div>
                                    <p class="text-xs font-medium mt-2 <?php echo e($esOperativo ? 'text-emerald-700' : ($esCondicional ? 'text-amber-700' : 'text-rose-700')); ?>">
                                        <?php echo e($esOperativo ? 'Ventana limpia' : ($esCondicional ? 'Precaución' : $razon)); ?>

                                    </p>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>

                <!-- Accesos Directos (Grid) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    
                    <!-- Maquinaria -->
                    <a href="<?php echo e(route('modulos.maquinaria')); ?>" class="group clima-card hover:shadow-xl transition-all duration-300 p-8 hover:-translate-y-1 cursor-pointer">
                        <div class="flex flex-col items-center text-center">
                            <div class="bg-blue-50 group-hover:bg-blue-100 p-6 rounded-2xl mb-4 transition-colors duration-300">
                                <svg class="w-12 h-12 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-bold text-slate-900 mb-2 group-hover:text-blue-600 transition-colors">Maquinaria</h3>
                            <p class="text-sm text-slate-500">GestiÃ³n de equipos y mantenimientos</p>
                        </div>
                    </a>

                    <!-- Inventario -->
                    <a href="<?php echo e(route('modulos.inventario-forestal')); ?>" class="group clima-card hover:shadow-xl transition-all duration-300 p-8 hover:-translate-y-1 cursor-pointer">
                        <div class="flex flex-col items-center text-center">
                            <div class="bg-purple-50 group-hover:bg-purple-100 p-6 rounded-2xl mb-4 transition-colors duration-300">
                                <svg class="w-12 h-12 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-bold text-slate-900 mb-2 group-hover:text-purple-600 transition-colors">Inventario</h3>
                            <p class="text-sm text-slate-500">Control de stock y materiales</p>
                        </div>
                    </a>

                    <!-- Personal -->
                    <a href="<?php echo e(route('modulos.personal')); ?>" class="group clima-card hover:shadow-xl transition-all duration-300 p-8 hover:-translate-y-1 cursor-pointer">
                        <div class="flex flex-col items-center text-center">
                            <div class="bg-orange-50 group-hover:bg-orange-100 p-6 rounded-2xl mb-4 transition-colors duration-300">
                                <svg class="w-12 h-12 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-bold text-slate-900 mb-2 group-hover:text-orange-600 transition-colors">Personal</h3>
                            <p class="text-sm text-slate-500">GestiÃ³n de empleados y asistencia</p>
                        </div>
                    </a>

                    <!-- Registrar Operaciones - CTA Principal -->
                    <a href="<?php echo e(route('modulos.operaciones')); ?>" class="group bg-gradient-to-br from-emerald-700 to-emerald-900 rounded-2xl shadow-md hover:shadow-2xl transition-all duration-300 p-8 hover:-translate-y-1 cursor-pointer relative overflow-hidden">
                        <!-- Efecto de brillo -->
                        <div class="absolute inset-0 bg-white opacity-0 group-hover:opacity-10 transition-opacity duration-300"></div>
                        
                        <div class="flex flex-col items-center text-center relative z-10">
                            <div class="bg-white bg-opacity-20 group-hover:bg-opacity-30 p-6 rounded-2xl mb-4 transition-all duration-300">
                                <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-bold text-white mb-2 group-hover:scale-105 transition-transform">Registrar Operaciones</h3>
                            <p class="text-sm text-emerald-100">AÃ±adir nueva operaciÃ³n forestal</p>
                        </div>
                        
                        <!-- Badge "Acción Principal" -->
                        <div class="absolute top-4 right-4">
                            <span class="bg-white bg-opacity-20 text-white text-xs font-semibold px-2 py-1 rounded-full">â˜…</span>
                        </div>
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-2xl shadow-sm p-12">
                <div class="flex flex-col items-center text-center">
                    <div class="bg-gray-100 p-6 rounded-full mb-4">
                        <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">No hay lotes configurados</h3>
                    <p class="text-gray-500 mb-6">Crea tu primer lote para comenzar a gestionar operaciones forestales</p>
                    <a href="<?php echo e(route('lotes.index')); ?>" class="bg-green-600 hover:bg-green-700 text-white font-medium px-6 py-3 rounded-xl shadow-sm hover:shadow-md transition-all duration-200">
                        Crear Lote
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5863877a5171c196453bfa0bd807e410)): ?>
<?php $attributes = $__attributesOriginal5863877a5171c196453bfa0bd807e410; ?>
<?php unset($__attributesOriginal5863877a5171c196453bfa0bd807e410); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5863877a5171c196453bfa0bd807e410)): ?>
<?php $component = $__componentOriginal5863877a5171c196453bfa0bd807e410; ?>
<?php unset($__componentOriginal5863877a5171c196453bfa0bd807e410); ?>
<?php endif; ?>



<?php /**PATH D:\trabajo_final\rennova\resources\views\dashboard.blade.php ENDPATH**/ ?>