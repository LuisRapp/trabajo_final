

<?php $__env->startSection('content'); ?>
<div class="container-fluid px-4 py-4">
    <div class="text-center mb-5">
        <h1 class="fw-bold text-secondary mb-2"><i class="bi bi-tree me-2"></i>Inventario Forestal</h1>
        <p class="text-muted lead">Gestiona lotes, clientes, ventas y productos forestales</p>
    </div>

    <div class="row g-4 justify-content-center">
        <!-- Lotes -->
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('ver-lotes')): ?>
        <div class="col-12 col-md-6 col-lg-4 col-xl-3">
            <div class="card h-100 border-0 shadow-sm hover-elevate">
                <div class="card-body text-center p-4">
                    <div class="mb-3" style="color: var(--primary-color);"><i class="bi bi-geo-alt display-4"></i></div>
                    <h5 class="card-title fw-bold">Lotes</h5>
                    <a href="<?php echo e(route('lotes.index')); ?>" class="btn btn-outline-success w-100 mt-3 stretched-link">Gestionar</a>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Clientes -->
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('ver-clientes')): ?>
        <div class="col-12 col-md-6 col-lg-4 col-xl-3">
            <div class="card h-100 border-0 shadow-sm hover-elevate">
                <div class="card-body text-center p-4">
                    <div class="mb-3" style="color: var(--primary-color);"><i class="bi bi-people display-4"></i></div>
                    <h5 class="card-title fw-bold">Clientes</h5>
                    <a href="<?php echo e(route('clientes.index')); ?>" class="btn btn-outline-success w-100 mt-3 stretched-link">Gestionar</a>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Ventas -->
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('ver-ventas')): ?>
        <div class="col-12 col-md-6 col-lg-4 col-xl-3">
            <div class="card h-100 border-0 shadow-sm hover-elevate">
                <div class="card-body text-center p-4">
                    <div class="mb-3" style="color: var(--primary-color);"><i class="bi bi-receipt display-4"></i></div>
                    <h5 class="card-title fw-bold">Ventas</h5>
                    <a href="<?php echo e(route('ventas.index')); ?>" class="btn btn-outline-success w-100 mt-3 stretched-link">Gestionar</a>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Cargas -->
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('ver-cargas')): ?>
        <div class="col-12 col-md-6 col-lg-4 col-xl-3">
            <div class="card h-100 border-0 shadow-sm hover-elevate">
                <div class="card-body text-center p-4">
                    <div class="mb-3" style="color: var(--primary-color);"><i class="bi bi-box-seam display-4"></i></div>
                    <h5 class="card-title fw-bold">Cargas</h5>
                    <a href="<?php echo e(route('cargas.index')); ?>" class="btn btn-outline-success w-100 mt-3 stretched-link">Gestionar</a>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Categorías -->
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('ver-categorias-madera')): ?>
        <div class="col-12 col-md-6 col-lg-4 col-xl-3">
            <div class="card h-100 border-0 shadow-sm hover-elevate">
                <div class="card-body text-center p-4">
                    <div class="mb-3" style="color: var(--primary-color);"><i class="bi bi-tree display-4"></i></div>
                    <h5 class="card-title fw-bold">Categorías de Madera</h5>
                    <a href="<?php echo e(route('categorias-madera.index')); ?>" class="btn btn-outline-success w-100 mt-3 stretched-link">Gestionar</a>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Lista de Precios -->
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('ver-lista-precios')): ?>
        <div class="col-12 col-md-6 col-lg-4 col-xl-3">
            <div class="card h-100 border-0 shadow-sm hover-elevate">
                <div class="card-body text-center p-4">
                    <div class="mb-3" style="color: var(--primary-color);"><i class="bi bi-tags display-4"></i></div>
                    <h5 class="card-title fw-bold">Lista de Precios</h5>
                    <a href="<?php echo e(route('lista-precios.index')); ?>" class="btn btn-outline-success w-100 mt-3 stretched-link">Gestionar</a>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\trabajo_final\rennova\resources\views/modulos/inventario-forestal.blade.php ENDPATH**/ ?>