<?php $__env->startSection('content'); ?>

<style>
    .modern-card {
        border: none;
        border-radius: 0.5rem;
        transition: all 0.3s ease;
        box-shadow: 0 1px 2px rgba(0,0,0,0.06);
    }
    .modern-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(0,0,0,0.08);
    }
    .kpi-card {
        background: white;
        border-radius: 0.5rem;
        padding: 1rem;
        border: none;
        box-shadow: 0 1px 2px rgba(0,0,0,0.06);
        transition: all 0.3s ease;
    }
    .kpi-card:hover {
        box-shadow: 0 4px 8px rgba(0,0,0,0.08);
    }
    .kpi-number {
        font-size: 1.75rem;
        font-weight: 700;
        line-height: 1;
    }
    .kpi-icon {
        width: 40px;
        height: 40px;
        border-radius: 0.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .weather-day {
        text-align: center;
        padding: 0.5rem;
    }
    .weather-icon-bg {
        width: 40px;
        height: 40px;
        border-radius: 0.5rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 0.5rem;
    }
    .weather-bar {
        height: 2px;
        border-radius: 1px;
        margin-top: 0.4rem;
    }
    .cta-card {
        background: linear-gradient(135deg, var(--primary-color) 0%, #1e5631 100%);
        position: relative;
        overflow: hidden;
    }
    .cta-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255,255,255,0);
        transition: background 0.3s ease;
    }
    .cta-card:hover::before {
        background: rgba(255,255,255,0.1);
    }
    .page-header {
        background: white;
        border-radius: 0.5rem;
        padding: 1rem;
        margin-bottom: 1rem;
        box-shadow: 0 1px 2px rgba(0,0,0,0.06);
    }
</style>

    
    <?php if(session('status')): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm mb-4 rounded-3">
            <i class="bi bi-check-circle-fill me-2"></i> <?php echo e(session('status')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if(session('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-4 rounded-3">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo e(session('error')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    
    <div class="page-header">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
            <div>
                <h1 class="h4 fw-bold mb-1">Panel de Control</h1>
                <p class="text-muted small mb-0" style="font-size: 0.8rem;">Gestión Forestal Rennova</p>
            </div>
            <div class="d-flex gap-2">
                <?php echo $__env->make('partials.selector-lote', ['lotes' => $lotes ?? collect(), 'loteSeleccionado' => $loteSeleccionado ?? null], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>
        </div>
    </div>

    
    <?php if(isset($pronosticoError) && $pronosticoError): ?>
        <div class="alert alert-warning rounded-3 mb-4">
            <i class="bi bi-exclamation-triangle me-2"></i> <?php echo e($pronosticoError); ?>

        </div>
    <?php endif; ?>

    <?php if(isset($pronosticoData) && !empty($pronosticoData)): ?>
        
        <div class="row g-2 mb-2">
            
            <div class="col-12 col-md-6">
                <div class="kpi-card">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <p class="text-uppercase text-muted fw-semibold mb-1" style="font-size: 0.7rem;">Días Perdidos</p>
                            <div class="kpi-number text-dark"><?php echo e($pronosticoData['analisisImpacto']['diasPerdidos'] ?? 0); ?></div>
                        </div>
                        <div class="kpi-icon bg-danger bg-opacity-10">
                            <i class="bi bi-exclamation-circle text-danger" style="font-size: 1.1rem;"></i>
                        </div>
                    </div>
                    <div class="pt-2 border-top">
                        <p class="text-uppercase text-muted fw-semibold mb-1" style="font-size: 0.7rem;">Período</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="small text-muted">Últimos 7 días</span>
                            <?php
                                $dias = $pronosticoData['analisisImpacto']['diasPerdidos'] ?? 0;
                            ?>
                            <?php if($dias > 5): ?>
                                <span class="badge bg-danger">Alto</span>
                            <?php elseif($dias > 2): ?>
                                <span class="badge bg-warning">Moderado</span>
                            <?php else: ?>
                                <span class="badge bg-success">Normal</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            
            <div class="col-12 col-md-6">
                <div class="kpi-card">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <p class="text-uppercase text-muted fw-semibold mb-1" style="font-size: 0.7rem;">Déficit TN</p>
                            <div class="kpi-number text-dark"><?php echo e($pronosticoData['analisisImpacto']['deficitTn'] ?? 0); ?></div>
                        </div>
                        <div class="kpi-icon bg-warning bg-opacity-10">
                            <i class="bi bi-graph-down text-warning" style="font-size: 1.1rem;"></i>
                        </div>
                    </div>
                    <?php if(isset($pronosticoData['analisisImpacto']['accionPorcentaje']) && $pronosticoData['analisisImpacto']['accionPorcentaje'] > 0): ?>
                        <div class="pt-2 border-top">
                            <p class="text-uppercase text-muted fw-semibold mb-1" style="font-size: 0.7rem;">Acción Recomendada</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="small text-muted">Aumentar producción</span>
                                <span class="fw-bold text-warning">+<?php echo e($pronosticoData['analisisImpacto']['accionPorcentaje']); ?>%</span>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        
        <?php
            $alerta = $pronosticoData['alerta'] ?? 'NORMAL';
            $accionRecomendada = $pronosticoData['accion_recomendada'] ?? null;
            $recomendacionDetallada = $pronosticoData['recomendacionDetallada'] ?? '';
            $mapaAcciones = [
                'AUMENTAR_PRODUCCION' => ['label' => 'Aumentar producción', 'color' => 'warning', 'icon' => 'bi-lightning-charge'],
                'MANTENIMIENTO_PREVENTIVO' => ['label' => 'Mantenimiento preventivo', 'color' => 'info', 'icon' => 'bi-tools'],
                'SUSPENSION_JORNADA' => ['label' => 'Suspender jornada', 'color' => 'danger', 'icon' => 'bi-pause-circle'],
                'OPERACION_NORMAL' => ['label' => 'Operación normal', 'color' => 'success', 'icon' => 'bi-check-circle'],
            ];
            $mapaAlertas = [
                'ACELERAR' => ['label' => 'Aumentar producción', 'color' => 'warning', 'icon' => 'bi-lightning-charge'],
                'SUSPENDER' => ['label' => 'Suspender operaciones', 'color' => 'danger', 'icon' => 'bi-pause-circle'],
                'NORMAL' => ['label' => 'Operación normal', 'color' => 'success', 'icon' => 'bi-check-circle'],
            ];
            $alertaInfo = $accionRecomendada && isset($mapaAcciones[$accionRecomendada])
                ? $mapaAcciones[$accionRecomendada]
                : ($mapaAlertas[$alerta] ?? $mapaAlertas['NORMAL']);
        ?>

        <div class="card modern-card mb-2">
            <div class="card-body p-3">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-2">
                    <h2 class="fw-bold mb-0" style="font-size: 0.9rem;">Camino recomendado</h2>
                    <span class="badge bg-<?php echo e($alertaInfo['color']); ?>">
                        <i class="bi <?php echo e($alertaInfo['icon']); ?> me-1"></i><?php echo e($alertaInfo['label']); ?>

                    </span>
                </div>

                <div class="row g-2">
                    <div class="col-12 col-md-4">
                        <div class="p-3 rounded-3 bg-<?php echo e($alertaInfo['color']); ?> bg-opacity-10 h-100">
                            <div class="text-uppercase text-muted fw-semibold" style="font-size: 0.65rem;">Estrategia</div>
                            <div class="fw-bold text-<?php echo e($alertaInfo['color']); ?>" style="font-size: 1rem;">
                                <?php echo e($alertaInfo['label']); ?>

                            </div>
                            <div class="small text-muted mt-1">Basado en clima y operatividad del lote seleccionado.</div>
                        </div>
                    </div>
                    <div class="col-12 col-md-8">
                        <div class="p-3 rounded-3 bg-light h-100">
                            <div class="text-uppercase text-muted fw-semibold" style="font-size: 0.65rem;">Detalle de recomendación</div>
                            <div class="small text-muted" style="white-space: pre-wrap;"><?php echo e($recomendacionDetallada ?: 'Sin detalle disponible.'); ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="card modern-card mb-2">
            <div class="card-body p-2">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h2 class="fw-bold mb-0" style="font-size: 0.9rem;">Pronóstico de Operatividad</h2>
                    <span class="text-muted" style="font-size: 0.75rem;">Próximos <?php echo e(count($pronosticoData['pronostico'] ?? [])); ?> días</span>
                </div>
                
                <div class="row g-3">
                    <?php $__currentLoopData = ($pronosticoData['pronostico'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dia): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $esOperativo = strtoupper($dia['estado'] ?? 'OPERATIVO') === 'OPERATIVO';
                            $esFinDeSemana = isset($dia['suelo']) && stripos($dia['suelo'], 'fin de semana') !== false;
                            $colorBg = $esFinDeSemana ? 'secondary' : ($esOperativo ? 'success' : 'danger');
                            $labelParts = explode('(', $dia['label'] ?? 'Día');
                            $diaNombre = trim($labelParts[0] ?? 'Día');
                            $fecha = isset($labelParts[1]) ? trim(str_replace(')', '', $labelParts[1])) : '';
                            $textoEstado = $esFinDeSemana ? 'No laboral' : ($esOperativo ? 'Operativo' : ($dia['suelo'] ?? 'Inactivo'));
                        ?>
                        <div class="col-6 col-sm-4 col-md-3 col-lg">
                            <div class="weather-day">
                                <p class="fw-semibold mb-0" style="font-size: 0.75rem;"><?php echo e($diaNombre); ?></p>
                                <p class="text-muted mb-2" style="font-size: 0.65rem;"><?php echo e($fecha); ?></p>
                                <div class="weather-icon-bg bg-<?php echo e($colorBg); ?> bg-opacity-10">
                                    <?php if($esFinDeSemana): ?>
                                        <i class="bi bi-calendar-x text-<?php echo e($colorBg); ?>" style="font-size: 1.2rem;"></i>
                                    <?php elseif($esOperativo): ?>
                                        <i class="bi bi-sun text-<?php echo e($colorBg); ?>" style="font-size: 1.2rem;"></i>
                                    <?php else: ?>
                                        <i class="bi bi-cloud-rain text-<?php echo e($colorBg); ?>" style="font-size: 1.2rem;"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="weather-bar bg-<?php echo e($colorBg); ?>"></div>
                                <p class="fw-medium text-<?php echo e($colorBg); ?> mt-1 mb-0" style="font-size: 0.7rem;">
                                    <?php echo e($textoEstado); ?>

                                </p>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    
    <div class="row g-2 mb-2">
        
        <div class="col-12 col-sm-6 col-lg-3">
            <a href="<?php echo e(route('modulos.maquinaria')); ?>" class="text-decoration-none">
                <div class="card modern-card h-100">
                    <div class="card-body text-center p-2">
                        <div class="kpi-icon bg-primary bg-opacity-10 mx-auto mb-2">
                            <i class="bi bi-truck text-primary" style="font-size: 1.2rem;"></i>
                        </div>
                        <h5 class="fw-bold mb-1 text-dark" style="font-size: 0.9rem;">Maquinaria</h5>
                        <p class="text-muted mb-0" style="font-size: 0.7rem;">Gestión de equipos</p>
                    </div>
                </div>
            </a>
        </div>

        
        <div class="col-12 col-sm-6 col-lg-3">
            <a href="<?php echo e(route('modulos.inventario-forestal')); ?>" class="text-decoration-none">
                <div class="card modern-card h-100">
                    <div class="card-body text-center p-2">
                        <div class="kpi-icon" style="background: rgba(139, 92, 246, 0.1); margin: 0 auto;" class="mb-2">
                            <i class="bi bi-box-seam" style="color: #8B5CF6; font-size: 1.2rem;"></i>
                        </div>
                        <h5 class="fw-bold mb-1 text-dark" style="font-size: 0.9rem;">Inventario</h5>
                        <p class="text-muted mb-0" style="font-size: 0.7rem;">Control de stock</p>
                    </div>
                </div>
            </a>
        </div>

        
        <div class="col-12 col-sm-6 col-lg-3">
            <a href="<?php echo e(route('modulos.personal')); ?>" class="text-decoration-none">
                <div class="card modern-card h-100">
                    <div class="card-body text-center p-2">
                        <div class="kpi-icon" style="background: rgba(249, 115, 22, 0.1); margin: 0 auto;" class="mb-2">
                            <i class="bi bi-people" style="color: #F97316; font-size: 1.2rem;"></i>
                        </div>
                        <h5 class="fw-bold mb-1 text-dark" style="font-size: 0.9rem;">Personal</h5>
                        <p class="text-muted mb-0" style="font-size: 0.7rem;">Gestión de empleados</p>
                    </div>
                </div>
            </a>
        </div>

        
        <div class="col-12 col-sm-6 col-lg-3">
            <a href="<?php echo e(route('modulos.operaciones')); ?>" class="text-decoration-none">
                <div class="card cta-card modern-card h-100 position-relative">
                    <div class="card-body text-center p-2">
                        <div class="kpi-icon mx-auto mb-2" style="background: rgba(255,255,255,0.2);">
                            <i class="bi bi-plus-lg text-white" style="font-size: 1.2rem;"></i>
                        </div>
                        <h5 class="fw-bold mb-1 text-white" style="font-size: 0.9rem;">Registrar Operaciones</h5>
                        <p class="text-white-50 mb-0" style="font-size: 0.7rem;">Nueva operación</p>
                    </div>
                    <div class="position-absolute top-0 end-0 m-3">
                        <span class="badge bg-light bg-opacity-25 text-white">★</span>
                    </div>
                </div>
            </a>
        </div>

        
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card modern-card h-100">
                <div class="card-body p-2">
                    <div class="text-center mb-2">
                        <div class="kpi-icon" style="background: rgba(59, 130, 246, 0.12); margin: 0 auto;">
                            <i class="bi bi-cloud-rain" style="color: #3B82F6; font-size: 1.2rem;"></i>
                        </div>
                        <h5 class="fw-bold mb-1 text-dark" style="font-size: 0.9rem;">Reporte de Lluvias</h5>
                        <p class="text-muted mb-2" style="font-size: 0.7rem;">Exportar PDF</p>
                    </div>
                    <form action="<?php echo e(route('reportes.clima-lluvias.pdf')); ?>" method="GET">
                        <div class="mb-2">
                            <label class="form-label mb-1" style="font-size: 0.7rem;">Lote</label>
                            <select name="id_lote" class="form-select form-select-sm">
                                <option value="">Todos</option>
                                <?php $__currentLoopData = $lotes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lote): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($lote->id_lote); ?>"><?php echo e($lote->propietario); ?> - <?php echo e($lote->ubicacion); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label mb-1" style="font-size: 0.7rem;">Desde</label>
                            <input type="date" name="desde" max="<?php echo e(\Carbon\Carbon::now()->toDateString()); ?>" class="form-control form-control-sm" value="<?php echo e(\Carbon\Carbon::now()->subDays(30)->toDateString()); ?>">
                        </div>
                        <div class="mb-2">
                            <label class="form-label mb-1" style="font-size: 0.7rem;">Hasta</label>
                            <input type="date" name="hasta" max="<?php echo e(\Carbon\Carbon::now()->toDateString()); ?>" class="form-control form-control-sm" value="<?php echo e(\Carbon\Carbon::now()->toDateString()); ?>">
                        </div>
                        <button type="submit" class="btn btn-sm btn-primary w-100">Descargar PDF</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/rluis/Escritorio/trabajo_final/rennova/resources/views/index.blade.php ENDPATH**/ ?>