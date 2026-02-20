

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="bi bi-clipboard-check me-2 text-success"></i>Operaciones Diarias
            </h1>
            <p class="text-muted mb-0">Gestiona proveedores, insumos y partes diarios de trabajo</p>
        </div>
    </div>

    <div class="row g-4">
        <!-- Proveedores -->
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('ver-proveedores')): ?>
        <div class="col-12 col-md-6 col-xl-4">
            <div class="card h-100 border-0 shadow-sm hover-elevate">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3">
                            <i class="bi bi-truck fs-3 text-success"></i>
                        </div>
                        <h5 class="card-title mb-0 fw-bold text-success">Proveedores</h5>
                    </div>
                    <p class="card-text text-muted">
                        Administración de proveedores y gestión de compras.
                    </p>
                    <a href="<?php echo e(route('proveedores.index')); ?>" class="btn btn-outline-success stretched-link w-100">
                        Gestionar
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Insumos -->
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('ver-insumos')): ?>
        <div class="col-12 col-md-6 col-xl-4">
            <div class="card h-100 border-0 shadow-sm hover-elevate">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3">
                            <i class="bi bi-box-seam fs-3 text-success"></i>
                        </div>
                        <h5 class="card-title mb-0 fw-bold text-success">Insumos</h5>
                    </div>
                    <p class="card-text text-muted">
                        Control de inventario de insumos y materiales.
                    </p>
                    <a href="<?php echo e(route('insumos.index')); ?>" class="btn btn-outline-success stretched-link w-100">
                        Gestionar
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Gestión Stock (FIFO) -->
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('ver-gestion-stock')): ?>
        <div class="col-12 col-md-6 col-xl-4">
            <div class="card h-100 border-0 shadow-sm hover-elevate">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3">
                            <i class="bi bi-boxes fs-3 text-success"></i>
                        </div>
                        <h5 class="card-title mb-0 fw-bold text-success">Gestión Stock (FIFO)</h5>
                    </div>
                    <p class="card-text text-muted">
                        Gestión avanzada de stock utilizando el método FIFO.
                    </p>
                    <a href="<?php echo e(route('modulos.operaciones.gestionstock')); ?>" class="btn btn-outline-success stretched-link w-100">
                        Abrir
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Partes Diarios -->
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('ver-partes-diarios')): ?>
        <div class="col-12 col-md-6 col-xl-4">
            <div class="card h-100 border-0 shadow-sm hover-elevate">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3">
                            <i class="bi bi-clipboard-check fs-3 text-success"></i>
                        </div>
                        <h5 class="card-title mb-0 fw-bold text-success">Partes Diarios</h5>
                    </div>
                    <p class="card-text text-muted">
                        Registro y control de partes diarios de trabajo.
                    </p>
                    <a href="<?php echo e(route('partes-diarios.index')); ?>" class="btn btn-outline-success stretched-link w-100">
                        Gestionar
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Unidades de Medida -->
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('ver-unidades-medida')): ?>
        <div class="col-12 col-md-6 col-xl-4">
            <div class="card h-100 border-0 shadow-sm hover-elevate">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3">
                            <i class="bi bi-rulers fs-3 text-success"></i>
                        </div>
                        <h5 class="card-title mb-0 fw-bold text-success">Unidades de Medida</h5>
                    </div>
                    <p class="card-text text-muted">
                        Configuración de unidades de medida del sistema.
                    </p>
                    <a href="<?php echo e(route('unidades-medida.index')); ?>" class="btn btn-outline-success stretched-link w-100">
                        Gestionar
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\trabajo_final\rennova\resources\views\modulos\operaciones.blade.php ENDPATH**/ ?>