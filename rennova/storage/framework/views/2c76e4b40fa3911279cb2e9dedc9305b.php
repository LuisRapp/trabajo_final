<?php $__env->startSection('content'); ?>
<div class="container-fluid px-4 py-4">
    <div class="text-center mb-5">
        <h1 class="fw-bold text-secondary mb-2"><i class="bi bi-truck me-2"></i>Gestión de Maquinaria</h1>
        <p class="text-muted lead">Administra tu flota de maquinaria, mantenimientos y costos operativos</p>
    </div>

    <div class="row g-4 justify-content-center">
        <!-- Maquinarias -->
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('ver-maquinarias')): ?>
        <div class="col-12 col-md-6 col-lg-4 col-xl-3">
            <div class="card h-100 border-0 shadow-sm hover-elevate">
                <div class="card-body text-center p-4">
                    <div class="mb-3" style="color: var(--primary-color);"><i class="bi bi-truck display-4"></i></div>
                    <h5 class="card-title fw-bold">Maquinarias</h5>
                    <a href="<?php echo e(route('maquinarias.index')); ?>" class="btn btn-outline-success w-100 mt-3 stretched-link">Gestionar</a>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Mantenimientos -->
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('ver-mantenimientos')): ?>
        <div class="col-12 col-md-6 col-lg-4 col-xl-3">
            <div class="card h-100 border-0 shadow-sm hover-elevate">
                <div class="card-body text-center p-4">
                    <div class="mb-3" style="color: var(--primary-color);"><i class="bi bi-tools display-4"></i></div>
                    <h5 class="card-title fw-bold">Mantenimientos</h5>
                    <a href="<?php echo e(route('mantenimientos.index')); ?>" class="btn btn-outline-success w-100 mt-3 stretched-link">Gestionar</a>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Kits -->
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('ver-kits-mantenimiento')): ?>
        <div class="col-12 col-md-6 col-lg-4 col-xl-3">
            <div class="card h-100 border-0 shadow-sm hover-elevate">
                <div class="card-body text-center p-4">
                    <div class="mb-3" style="color: var(--primary-color);"><i class="bi bi-gear-fill display-4"></i></div>
                    <h5 class="card-title fw-bold">Kits de Mantenimiento</h5>
                    <a href="<?php echo e(route('kits-mantenimiento.index')); ?>" class="btn btn-outline-success w-100 mt-3 stretched-link">Gestionar</a>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Costos -->
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('ver-historico-costos-maquinarias')): ?>
        <div class="col-12 col-md-6 col-lg-4 col-xl-3">
            <div class="card h-100 border-0 shadow-sm hover-elevate">
                <div class="card-body text-center p-4">
                    <div class="mb-3" style="color: var(--primary-color);"><i class="bi bi-graph-up display-4"></i></div>
                    <h5 class="card-title fw-bold">Costos de Maquinaria</h5>
                    <a href="<?php echo e(route('historico-costos-maquinarias.index')); ?>" class="btn btn-outline-success w-100 mt-3 stretched-link">Ver Histórico</a>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Tipos -->
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('ver-tipos-maquinaria')): ?>
        <div class="col-12 col-md-6 col-lg-4 col-xl-3">
            <div class="card h-100 border-0 shadow-sm hover-elevate">
                <div class="card-body text-center p-4">
                    <div class="mb-3" style="color: var(--primary-color);"><i class="bi bi-gear-wide-connected display-4"></i></div>
                    <h5 class="card-title fw-bold">Tipos de Maquinaria</h5>
                    <a href="<?php echo e(route('tipos-maquinaria.index')); ?>" class="btn btn-outline-success w-100 mt-3 stretched-link">Gestionar</a>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('configurar-notificaciones-mantenimiento')): ?>
        <!-- Notificaciones -->
        <div class="col-12 col-md-6 col-lg-4 col-xl-3">
            <div class="card h-100 border-0 shadow-sm hover-elevate">
                <div class="card-body text-center p-4">
                    <div class="mb-3" style="color: var(--primary-color);"><i class="bi bi-bell-fill display-4"></i></div>
                    <h5 class="card-title fw-bold">Configuración de Notificaciones</h5>
                    <a href="<?php echo e(route('configuracion-notificaciones.index')); ?>" class="btn btn-outline-success w-100 mt-3 stretched-link">Configurar</a>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/rluis/Escritorio/trabajo_final/rennova/resources/views/modulos/maquinaria.blade.php ENDPATH**/ ?>