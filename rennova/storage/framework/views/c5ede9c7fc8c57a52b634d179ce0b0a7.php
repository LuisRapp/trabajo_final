

<?php $__env->startSection('content'); ?>

    
    <?php if(session('status')): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm mb-4">
            <i class="bi bi-check-circle-fill me-2"></i> <?php echo e(session('status')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if(session('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-4">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo e(session('error')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    
    <?php echo $__env->make('partials.selector-lote', ['lotes' => $lotes ?? collect(), 'loteSeleccionado' => $loteSeleccionado ?? null], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    
    <?php if(isset($pronosticoError) && $pronosticoError): ?>
        <div class="alert alert-warning small"><?php echo e($pronosticoError); ?></div>
    <?php endif; ?>

    <?php if(isset($pronosticoData) && !empty($pronosticoData)): ?>
        <div class="mb-5">
            <?php if (isset($component)) { $__componentOriginal8e533a9f8b46dcc010c770ef91e454a9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8e533a9f8b46dcc010c770ef91e454a9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.clima.pronostico','data' => ['alerta' => $pronosticoData['alerta'] ?? 'NORMAL','pronostico' => $pronosticoData['pronostico'] ?? [],'analisisImpacto' => $pronosticoData['analisisImpacto'] ?? [],'lote' => $pronosticoData['loteNombre'] ?? 'Desconocido']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('clima.pronostico'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['alerta' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($pronosticoData['alerta'] ?? 'NORMAL'),'pronostico' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($pronosticoData['pronostico'] ?? []),'analisisImpacto' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($pronosticoData['analisisImpacto'] ?? []),'lote' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($pronosticoData['loteNombre'] ?? 'Desconocido')]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8e533a9f8b46dcc010c770ef91e454a9)): ?>
<?php $attributes = $__attributesOriginal8e533a9f8b46dcc010c770ef91e454a9; ?>
<?php unset($__attributesOriginal8e533a9f8b46dcc010c770ef91e454a9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8e533a9f8b46dcc010c770ef91e454a9)): ?>
<?php $component = $__componentOriginal8e533a9f8b46dcc010c770ef91e454a9; ?>
<?php unset($__componentOriginal8e533a9f8b46dcc010c770ef91e454a9); ?>
<?php endif; ?>
        </div>
    <?php endif; ?>

    
    <div>
        <h3 class="fw-bold text-secondary mb-4 opacity-75">Panel de Control</h3>

        
        <h6 class="text-uppercase text-muted fw-bold mb-3 small">Módulos Operativos</h6>
        <div class="row g-4 mb-5">
            <!-- Maquinaria -->
            <div class="col-12 col-md-6 col-lg-3">
                <div class="card h-100 border-0 shadow-sm hover-elevate">
                    <div class="card-body text-center p-4">
                        <div class="mb-3" style="color: var(--primary-color);"><i class="bi bi-truck display-4"></i></div>
                        <h5 class="card-title fw-bold">Maquinaria</h5>
                        <p class="text-muted small">Gestión de flota</p>
                        <a href="<?php echo e(route('modulos.maquinaria')); ?>" class="btn btn-outline-success w-100 stretched-link">Ingresar</a>
                    </div>
                </div>
            </div>

            <!-- Inventario -->
            <div class="col-12 col-md-6 col-lg-3">
                <div class="card h-100 border-0 shadow-sm hover-elevate">
                    <div class="card-body text-center p-4">
                        <div class="mb-3" style="color: var(--primary-color);"><i class="bi bi-tree display-4"></i></div>
                        <h5 class="card-title fw-bold">Inventario</h5>
                        <p class="text-muted small">Control de lotes</p>
                        <a href="<?php echo e(route('modulos.inventario-forestal')); ?>" class="btn btn-outline-success w-100 stretched-link">Ingresar</a>
                    </div>
                </div>
            </div>

            <!-- Personal -->
            <div class="col-12 col-md-6 col-lg-3">
                <div class="card h-100 border-0 shadow-sm hover-elevate">
                    <div class="card-body text-center p-4">
                        <div class="mb-3" style="color: var(--primary-color);"><i class="bi bi-people display-4"></i></div>
                        <h5 class="card-title fw-bold">Personal</h5>
                        <p class="text-muted small">RRHH y legajos</p>
                        <a href="<?php echo e(route('modulos.personal')); ?>" class="btn btn-outline-success w-100 stretched-link">Ingresar</a>
                    </div>
                </div>
            </div>

            <!-- Operaciones -->
            <div class="col-12 col-md-6 col-lg-3">
                <div class="card h-100 border-0 shadow-sm hover-elevate bg-success bg-opacity-10">
                    <div class="card-body text-center p-4">
                        <div class="mb-3" style="color: var(--primary-color);"><i class="bi bi-clipboard-check display-4"></i></div>
                        <h5 class="card-title fw-bold text-success">Operaciones</h5>
                        <p class="text-muted small">Producción diaria</p>
                        <a href="<?php echo e(route('modulos.operaciones')); ?>" class="btn btn-success w-100 stretched-link">Registrar</a>
                    </div>
                </div>
            </div>
        </div>

        
        <h6 class="text-uppercase text-muted fw-bold mb-3 small">Gestión y Análisis</h6>
        <div class="row g-4">
            <!-- Reportes -->
            <div class="col-12 col-md-6 col-lg-3">
                <div class="card h-100 border-0 shadow-sm hover-elevate">
                    <div class="card-body p-3 d-flex align-items-center">
                        <div class="bg-light rounded p-3 me-3 text-primary"><i class="bi bi-bar-chart fs-2"></i></div>
                        <div>
                            <h6 class="fw-bold mb-1">Reportes</h6>
                            <a href="<?php echo e(route('reportes.estadisticas-forestales')); ?>" class="text-decoration-none small stretched-link">Ver costos e históricos</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stock -->
            <div class="col-12 col-md-6 col-lg-3">
                <div class="card h-100 border-0 shadow-sm hover-elevate">
                    <div class="card-body p-3 d-flex align-items-center">
                        <div class="bg-light rounded p-3 me-3 text-warning"><i class="bi bi-box-seam fs-2"></i></div>
                        <div>
                            <h6 class="fw-bold mb-1">Stock (FIFO)</h6>
                            <a href="<?php echo e(route('modulos.operaciones.gestionstock')); ?>" class="text-decoration-none small stretched-link">Gestionar Insumos</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Configuración -->
            <div class="col-12 col-md-6 col-lg-3">
                <div class="card h-100 border-0 shadow-sm hover-elevate">
                    <div class="card-body p-3 d-flex align-items-center">
                        <div class="bg-light rounded p-3 me-3 text-secondary"><i class="bi bi-gear fs-2"></i></div>
                        <div>
                            <h6 class="fw-bold mb-1">Configuración</h6>
                            <a href="<?php echo e(route('modulos.administracion')); ?>" class="text-decoration-none small stretched-link">Ajustes del sistema</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/index.blade.php ENDPATH**/ ?>