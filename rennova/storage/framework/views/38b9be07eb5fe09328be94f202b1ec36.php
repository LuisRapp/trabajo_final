<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="bi bi-shield-lock me-2 text-success"></i>Administración
            </h1>
            <p class="text-muted mb-0">Gestión de usuarios, roles, permisos y configuraciones del sistema</p>
        </div>
    </div>

    <div class="row g-4">
        <!-- Usuarios -->
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('gestionar-usuarios')): ?>
        <div class="col-12 col-md-6 col-xl-4">
            <div class="card h-100 border-0 shadow-sm hover-elevate">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3">
                            <i class="bi bi-people fs-3 text-success"></i>
                        </div>
                        <h5 class="card-title mb-0 fw-bold text-success">Usuarios</h5>
                    </div>
                    <p class="card-text text-muted">
                        Gestión de cuentas de usuario y accesos al sistema.
                    </p>
                    <a href="<?php echo e(route('usuarios.index')); ?>" class="btn btn-outline-success stretched-link w-100">
                        Gestionar
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Roles y Permisos -->
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('gestionar-permisos')): ?>
        <div class="col-12 col-md-6 col-xl-4">
            <div class="card h-100 border-0 shadow-sm hover-elevate">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3">
                            <i class="bi bi-shield-check fs-3 text-success"></i>
                        </div>
                        <h5 class="card-title mb-0 fw-bold text-success">Roles y Permisos</h5>
                    </div>
                    <p class="card-text text-muted">
                        Configuración de roles y asignación de permisos.
                    </p>
                    <a href="<?php echo e(route('roles-permisos.index')); ?>" class="btn btn-outline-success stretched-link w-100">
                        Gestionar
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Auditorías -->
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('ver-auditoria')): ?>
        <div class="col-12 col-md-6 col-xl-4">
            <div class="card h-100 border-0 shadow-sm hover-elevate">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3">
                            <i class="bi bi-file-earmark-text fs-3 text-success"></i>
                        </div>
                        <h5 class="card-title mb-0 fw-bold text-success">Auditorías</h5>
                    </div>
                    <p class="card-text text-muted">
                        Registro y consulta de actividades del sistema.
                    </p>
                    <a href="<?php echo e(route('auditorias.index')); ?>" class="btn btn-outline-success stretched-link w-100">
                        Ver Historial
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Configuración de Mantenimiento -->
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('configurar-mantenimiento')): ?>
        <div class="col-12 col-md-6 col-xl-4">
            <div class="card h-100 border-0 shadow-sm hover-elevate">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3">
                            <i class="bi bi-calendar-check fs-3 text-success"></i>
                        </div>
                        <h5 class="card-title mb-0 fw-bold text-success">Configuración Mantenimiento</h5>
                    </div>
                    <p class="card-text text-muted">
                        Ajustes y parámetros para el módulo de mantenimiento.
                    </p>
                    <a href="<?php echo e(route('configuracion-mantenimiento.index')); ?>" class="btn btn-outline-success stretched-link w-100">
                        Configurar
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/rluis/Escritorio/trabajo_final/rennova/resources/views/modulos/administracion.blade.php ENDPATH**/ ?>