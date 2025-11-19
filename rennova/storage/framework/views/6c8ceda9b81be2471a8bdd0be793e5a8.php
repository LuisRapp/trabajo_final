

<?php $__env->startSection('content'); ?>
<style>
    body, .main-content.bg-light {
        background: #F4F7F6 !important;
    }
    .erp-module-title {
        font-size: 2.2rem;
        font-weight: 700;
        color: #2A6041;
        margin-bottom: 2rem;
        margin-top: 1.5rem;
        text-align: center;
    }
    .erp-card-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 2.2rem;
        justify-content: center;
    }
    .erp-nav-card {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 2px 12px rgba(44, 62, 80, 0.07);
        padding: 2.2rem 2rem 1.7rem 2rem;
        min-width: 240px;
        max-width: 270px;
        flex: 1 1 240px;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        margin-bottom: 1.5rem;
    }
    .erp-nav-card .erp-icon {
        font-size: 2.2rem;
        color: #2A6041;
        margin-bottom: 0.7rem;
    }
    .erp-nav-card .card-title {
        color: #2A6041;
        font-size: 1.18rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }
    .erp-nav-card .card-btn {
        background: #2A6041;
        color: #fff;
        border: none;
        border-radius: 7px;
        padding: 0.45rem 1.2rem;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        margin-top: 1.1rem;
        transition: background 0.13s;
        text-decoration: none;
        display: inline-block;
        box-shadow: 0 1px 4px rgba(44, 62, 80, 0.07);
    }
    .erp-nav-card .card-btn:hover {
        background: #1e4630;
        color: #fff;
    }
    @media (max-width: 900px) {
        .erp-card-grid { gap: 1rem; }
        .erp-nav-card { padding: 1.2rem 1rem; min-width: 180px; max-width: 100%; }
    }
</style>

<div style="margin-top: 4.5rem;">
    <div class="erp-module-title">
        <i class="bi bi-shield-lock"></i> Administración
    </div>

    <div class="erp-card-grid">
        <!-- Card: Usuarios -->
        <div class="erp-nav-card">
            <div class="erp-icon"><i class="bi bi-people"></i></div>
            <div class="card-title">Usuarios</div>
            <a href="<?php echo e(route('usuarios.index')); ?>" class="card-btn">Gestionar</a>
        </div>

        <!-- Card: Roles y Permisos -->
        <div class="erp-nav-card">
            <div class="erp-icon"><i class="bi bi-shield-check"></i></div>
            <div class="card-title">Roles y Permisos</div>
            <a href="<?php echo e(route('roles-permisos.index')); ?>" class="card-btn">Gestionar</a>
        </div>

        <!-- Card: Auditorías -->
        <div class="erp-nav-card">
            <div class="erp-icon"><i class="bi bi-file-earmark-text"></i></div>
            <div class="card-title">Auditorías</div>
            <a href="<?php echo e(route('auditorias.index')); ?>" class="card-btn">Ver Historial</a>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\trabajo_final\rennova\resources\views/modulos/administracion.blade.php ENDPATH**/ ?>