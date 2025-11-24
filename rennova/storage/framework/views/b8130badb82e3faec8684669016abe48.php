

<?php $__env->startSection('content'); ?>
<style>
    body, .main-content.bg-light {
        background: #F4F7F6 !important;
    }
    .module-title {
        font-size: 2.5rem;
        font-weight: 700;
        color: #2A6041;
        margin-bottom: 1rem;
        text-align: center;
    }
    .module-subtitle {
        font-size: 1.1rem;
        color: #6c757d;
        margin-bottom: 3rem;
        text-align: center;
    }
    .erp-card-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 2.2rem;
        justify-content: center;
        max-width: 1200px;
        margin: 0 auto;
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
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .erp-nav-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 20px rgba(44, 62, 80, 0.15);
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
        padding: 0.5rem 1.5rem;
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
</style>

<div class="container py-4">
    <h1 class="module-title"><i class="bi bi-tree"></i> Inventario Forestal</h1>
    <p class="module-subtitle">Gestiona lotes, clientes, ventas y productos forestales</p>

    <div class="erp-card-grid">
        <div class="erp-nav-card">
            <div class="erp-icon"><i class="bi bi-geo-alt"></i></div>
            <div class="card-title">Lotes</div>
            <a href="<?php echo e(route('lotes.index')); ?>" class="card-btn">Gestionar</a>
        </div>

        <div class="erp-nav-card">
            <div class="erp-icon"><i class="bi bi-people"></i></div>
            <div class="card-title">Clientes</div>
            <a href="<?php echo e(route('clientes.index')); ?>" class="card-btn">Gestionar</a>
        </div>

        <div class="erp-nav-card">
            <div class="erp-icon"><i class="bi bi-receipt"></i></div>
            <div class="card-title">Ventas</div>
            <a href="<?php echo e(route('ventas.index')); ?>" class="card-btn">Gestionar</a>
        </div>

        <div class="erp-nav-card">
            <div class="erp-icon"><i class="bi bi-box-seam"></i></div>
            <div class="card-title">Cargas</div>
            <a href="<?php echo e(route('cargas.index')); ?>" class="card-btn">Gestionar</a>
        </div>

        <div class="erp-nav-card">
            <div class="erp-icon"><i class="bi bi-tree"></i></div>
            <div class="card-title">Categorías de Madera</div>
            <a href="<?php echo e(route('categorias-madera.index')); ?>" class="card-btn">Gestionar</a>
        </div>

        <div class="erp-nav-card">
            <div class="erp-icon"><i class="bi bi-tags"></i></div>
            <div class="card-title">Lista de Precios</div>
            <a href="<?php echo e(route('lista-precios.index')); ?>" class="card-btn">Gestionar</a>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\trabajo_final\rennova\resources\views/modulos/inventario-forestal.blade.php ENDPATH**/ ?>