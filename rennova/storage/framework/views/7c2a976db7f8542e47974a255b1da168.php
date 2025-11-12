

<?php $__env->startSection('content'); ?>
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Panel Principal</h1>
        <button class="btn btn-outline-primary" data-bs-toggle="offcanvas" data-bs-target="#offcanvasAbms">Abrir ABMs</button>
    </div>

    <div class="row g-3">
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Operaciones</h5>
                    <p class="card-text">Ingreso rápido a módulos operativos.</p>
                    <div class="d-grid gap-2">
                        <a href="<?php echo e(route('cargas.index')); ?>" class="btn btn-primary">Cargas</a>
                        <a href="<?php echo e(route('partes-diarios.index')); ?>" class="btn btn-outline-primary">Partes Diarios</a>
                        <a href="<?php echo e(route('ventas.index')); ?>" class="btn btn-outline-primary">Ventas</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Maquinaria e Insumos</h5>
                    <p class="card-text">Recursos de producción y mantenimiento.</p>
                    <div class="d-grid gap-2">
                        <a href="<?php echo e(route('maquinarias.index')); ?>" class="btn btn-primary">Maquinarias</a>
                        <a href="<?php echo e(route('mantenimientos.index')); ?>" class="btn btn-outline-primary">Mantenimientos</a>
                        <a href="<?php echo e(route('insumos.index')); ?>" class="btn btn-outline-primary">Insumos</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Personas</h5>
                    <p class="card-text">Gestión de actores y relaciones.</p>
                    <div class="d-grid gap-2">
                        <a href="<?php echo e(route('clientes.index')); ?>" class="btn btn-primary">Clientes</a>
                        <a href="<?php echo e(route('choferes.index')); ?>" class="btn btn-outline-primary">Choferes</a>
                        <a href="<?php echo e(route('empleados.index')); ?>" class="btn btn-outline-primary">Empleados</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Configuración</h5>
                    <p class="card-text">Parámetros del sistema.</p>
                    <div class="d-grid gap-2">
                        <a href="<?php echo e(route('categorias-madera.index')); ?>" class="btn btn-primary">Categorías Madera</a>
                        <a href="<?php echo e(route('unidades-medida.index')); ?>" class="btn btn-outline-primary">Unidades de Medida</a>
                        <a href="<?php echo e(route('tipos-maquinaria.index')); ?>" class="btn btn-outline-primary">Tipos Maquinaria</a>
                        <a href="<?php echo e(route('roles-laborales.index')); ?>" class="btn btn-outline-primary">Roles Laborales</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Históricos</h5>
                    <p class="card-text">Seguimiento y evolución.</p>
                    <div class="d-grid gap-2">
                        <a href="<?php echo e(route('historico-costos-maquinarias.index')); ?>" class="btn btn-primary">Costos Maquinarias</a>
                        <a href="<?php echo e(route('historico-roles-laborales.index')); ?>" class="btn btn-outline-primary">Roles Laborales</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\trabajo_final\rennova\resources\views/index.blade.php ENDPATH**/ ?>