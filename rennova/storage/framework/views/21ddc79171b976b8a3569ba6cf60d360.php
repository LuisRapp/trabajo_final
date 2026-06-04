<?php $__env->startSection('content'); ?>
<div class="container-fluid px-4 py-4">
    <div class="text-center mb-5">
        <h1 class="fw-bold text-secondary mb-2"><i class="bi bi-people me-2"></i>Gestión de Personal</h1>
        <p class="text-muted lead">Administra empleados, choferes, pagos y asignaciones</p>
    </div>

    <div class="row g-4 justify-content-center">
        <!-- Empleados -->
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('ver-empleados')): ?>
        <div class="col-12 col-md-6 col-lg-4 col-xl-3">
            <div class="card h-100 border-0 shadow-sm hover-elevate">
                <div class="card-body text-center p-4">
                    <div class="mb-3" style="color: var(--primary-color);"><i class="bi bi-person-workspace display-4"></i></div>
                    <h5 class="card-title fw-bold">Empleados</h5>
                    <a href="<?php echo e(route('empleados.index')); ?>" class="btn btn-outline-success w-100 mt-3 stretched-link">Gestionar</a>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Choferes -->
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('ver-choferes')): ?>
        <div class="col-12 col-md-6 col-lg-4 col-xl-3">
            <div class="card h-100 border-0 shadow-sm hover-elevate">
                <div class="card-body text-center p-4">
                    <div class="mb-3" style="color: var(--primary-color);"><i class="bi bi-person-vcard display-4"></i></div>
                    <h5 class="card-title fw-bold">Choferes</h5>
                    <a href="<?php echo e(route('choferes.index')); ?>" class="btn btn-outline-success w-100 mt-3 stretched-link">Gestionar</a>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Adelantos -->
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('ver-adelantos')): ?>
        <div class="col-12 col-md-6 col-lg-4 col-xl-3">
            <div class="card h-100 border-0 shadow-sm hover-elevate">
                <div class="card-body text-center p-4">
                    <div class="mb-3" style="color: var(--primary-color);"><i class="bi bi-cash-coin display-4"></i></div>
                    <h5 class="card-title fw-bold">Adelantos</h5>
                    <a href="<?php echo e(route('adelantos.index')); ?>" class="btn btn-outline-success w-100 mt-3 stretched-link">Gestionar</a>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Recibos -->
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('ver-recibos')): ?>
        <div class="col-12 col-md-6 col-lg-4 col-xl-3">
            <div class="card h-100 border-0 shadow-sm hover-elevate">
                <div class="card-body text-center p-4">
                    <div class="mb-3" style="color: var(--primary-color);"><i class="bi bi-file-earmark-text display-4"></i></div>
                    <h5 class="card-title fw-bold">Recibos</h5>
                    <a href="<?php echo e(route('recibos.index')); ?>" class="btn btn-outline-success w-100 mt-3 stretched-link">Gestionar</a>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Liquidación -->
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('ver-liquidacion-pagos')): ?>
        <div class="col-12 col-md-6 col-lg-4 col-xl-3">
            <div class="card h-100 border-0 shadow-sm hover-elevate">
                <div class="card-body text-center p-4">
                    <div class="mb-3" style="color: var(--primary-color);"><i class="bi bi-calculator display-4"></i></div>
                    <h5 class="card-title fw-bold">Liquidación de Pagos</h5>
                    <a href="<?php echo e(route('liquidacion-pagos.index')); ?>" class="btn btn-outline-success w-100 mt-3 stretched-link">Gestionar</a>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Asignaciones -->
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('ver-asignaciones-lote')): ?>
        <div class="col-12 col-md-6 col-lg-4 col-xl-3">
            <div class="card h-100 border-0 shadow-sm hover-elevate">
                <div class="card-body text-center p-4">
                    <div class="mb-3" style="color: var(--primary-color);"><i class="bi bi-link-45deg display-4"></i></div>
                    <h5 class="card-title fw-bold">Asignaciones por Lote</h5>
                    <a href="<?php echo e(route('asignaciones-lote.index')); ?>" class="btn btn-outline-success w-100 mt-3 stretched-link">Gestionar</a>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Roles -->
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('ver-roles-laborales')): ?>
        <div class="col-12 col-md-6 col-lg-4 col-xl-3">
            <div class="card h-100 border-0 shadow-sm hover-elevate">
                <div class="card-body text-center p-4">
                    <div class="mb-3" style="color: var(--primary-color);"><i class="bi bi-person-badge display-4"></i></div>
                    <h5 class="card-title fw-bold">Roles Laborales</h5>
                    <a href="<?php echo e(route('roles-laborales.index')); ?>" class="btn btn-outline-success w-100 mt-3 stretched-link">Gestionar</a>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Histórico Roles -->
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('ver-roles-laborales')): ?>
        <div class="col-12 col-md-6 col-lg-4 col-xl-3">
            <div class="card h-100 border-0 shadow-sm hover-elevate">
                <div class="card-body text-center p-4">
                    <div class="mb-3" style="color: var(--primary-color);"><i class="bi bi-clock-history display-4"></i></div>
                    <h5 class="card-title fw-bold">Histórico Roles</h5>
                    <a href="<?php echo e(route('historico-roles-laborales.index')); ?>" class="btn btn-outline-success w-100 mt-3 stretched-link">Ver Histórico</a>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/rluis/Escritorio/trabajo_final/rennova/resources/views/modulos/personal.blade.php ENDPATH**/ ?>