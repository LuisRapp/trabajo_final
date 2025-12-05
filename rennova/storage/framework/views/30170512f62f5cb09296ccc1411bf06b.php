<aside class="sidebar" id="sidebar">
    <div class="py-2">
        <button class="sidebar-collapse-btn" type="button" data-bs-toggle="collapse" data-bs-target="#menuPrincipal">
            <span><i class="bi bi-star-fill me-2"></i> Principal</span> <i class="bi bi-chevron-down"></i>
        </button>
        <div class="collapse show" id="menuPrincipal">
            <a href="<?php echo e(route('lotes.index')); ?>" class="sidebar-link ms-3"><i class="bi bi-geo-alt"></i> Lotes</a>
            <a href="<?php echo e(route('clientes.index')); ?>" class="sidebar-link ms-3"><i class="bi bi-people"></i> Clientes</a>
            <a href="<?php echo e(route('proveedores.index')); ?>" class="sidebar-link ms-3"><i class="bi bi-truck"></i> Proveedores</a>
            <a href="<?php echo e(route('ventas.index')); ?>" class="sidebar-link ms-3"><i class="bi bi-receipt"></i> Ventas</a>
            <a href="<?php echo e(route('cargas.index')); ?>" class="sidebar-link ms-3"><i class="bi bi-box-seam"></i> Cargas</a>
            <a href="<?php echo e(route('choferes.index')); ?>" class="sidebar-link ms-3"><i class="bi bi-person-vcard"></i> Choferes</a>
        </div>

        <button class="sidebar-collapse-btn" type="button" data-bs-toggle="collapse" data-bs-target="#menuRecursos">
            <span><i class="bi bi-tools me-2"></i> Recursos</span> <i class="bi bi-chevron-down"></i>
        </button>
        <div class="collapse" id="menuRecursos">
            <a href="<?php echo e(route('insumos.index')); ?>" class="sidebar-link ms-3"><i class="bi bi-box-seam"></i> Insumos</a>
            <a href="<?php echo e(route('maquinarias.index')); ?>" class="sidebar-link ms-3"><i class="bi bi-truck"></i> Maquinarias</a>
            <a href="<?php echo e(route('mantenimientos.index')); ?>" class="sidebar-link ms-3"><i class="bi bi-tools"></i> Mantenimientos</a>
            <a href="<?php echo e(route('kits-mantenimiento.index')); ?>" class="sidebar-link ms-3"><i class="bi bi-gear-fill"></i> Kits de Mantenimiento</a>
        </div>

        <button class="sidebar-collapse-btn" type="button" data-bs-toggle="collapse" data-bs-target="#menuPersonal">
            <span><i class="bi bi-people-fill me-2"></i> Personal</span> <i class="bi bi-chevron-down"></i>
        </button>
        <div class="collapse" id="menuPersonal">
            <a href="<?php echo e(route('empleados.index')); ?>" class="sidebar-link ms-3"><i class="bi bi-person-workspace"></i> Empleados</a>
            <a href="<?php echo e(route('adelantos.index')); ?>" class="sidebar-link ms-3"><i class="bi bi-cash-coin"></i> Adelantos</a>
            <a href="<?php echo e(route('recibos.index')); ?>" class="sidebar-link ms-3"><i class="bi bi-file-earmark-text"></i> Recibos</a>
            <a href="<?php echo e(route('liquidacion-pagos.index')); ?>" class="sidebar-link ms-3"><i class="bi bi-calculator"></i> Liquidación de Pagos</a>
            <a href="<?php echo e(route('asignaciones-lote.index')); ?>" class="sidebar-link ms-3"><i class="bi bi-link-45deg"></i> Asignaciones por Lote</a>
        </div>

        <button class="sidebar-collapse-btn" type="button" data-bs-toggle="collapse" data-bs-target="#menuOperaciones">
            <span><i class="bi bi-clipboard-check me-2"></i> Operaciones</span> <i class="bi bi-chevron-down"></i>
        </button>
        <div class="collapse" id="menuOperaciones">
            <a href="<?php echo e(route('partes-diarios.index')); ?>" class="sidebar-link ms-3"><i class="bi bi-clipboard-check"></i> Partes Diarios</a>
        </div>

        <button class="sidebar-collapse-btn" type="button" data-bs-toggle="collapse" data-bs-target="#menuHistoricos">
            <span><i class="bi bi-clock-history me-2"></i> Históricos</span> <i class="bi bi-chevron-down"></i>
        </button>
        <div class="collapse" id="menuHistoricos">
            <a href="<?php echo e(route('historico-costos-maquinarias.index')); ?>" class="sidebar-link ms-3"><i class="bi bi-graph-up"></i> Costos Maquinarias</a>
            <a href="<?php echo e(route('historico-roles-laborales.index')); ?>" class="sidebar-link ms-3"><i class="bi bi-person-badge"></i> Roles Laborales</a>
            <a href="<?php echo e(route('auditorias.index')); ?>" class="sidebar-link ms-3"><i class="bi bi-file-earmark-text"></i> Auditorías</a>
        </div>

        <button class="sidebar-collapse-btn" type="button" data-bs-toggle="collapse" data-bs-target="#menuConfiguracion">
            <span><i class="bi bi-gear-fill me-2"></i> Configuración</span> <i class="bi bi-chevron-down"></i>
        </button>
        <div class="collapse" id="menuConfiguracion">
            <a href="<?php echo e(route('categorias-madera.index')); ?>" class="sidebar-link ms-3"><i class="bi bi-tree"></i> Categorías Madera</a>
            <a href="<?php echo e(route('lista-precios.index')); ?>" class="sidebar-link ms-3"><i class="bi bi-tags"></i> Lista de Precios</a>
            <a href="<?php echo e(route('unidades-medida.index')); ?>" class="sidebar-link ms-3"><i class="bi bi-rulers"></i> Unidades de Medida</a>
            <a href="<?php echo e(route('tipos-maquinaria.index')); ?>" class="sidebar-link ms-3"><i class="bi bi-gear-wide-connected"></i> Tipos Maquinaria</a>
            <a href="<?php echo e(route('roles-laborales.index')); ?>" class="sidebar-link ms-3"><i class="bi bi-person-badge"></i> Roles Laborales</a>
            <a href="<?php echo e(route('usuarios.index')); ?>" class="sidebar-link ms-3"><i class="bi bi-person-circle"></i> Usuarios</a>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('gestionar-permisos')): ?>
            <a href="<?php echo e(route('roles-permisos.index')); ?>" class="sidebar-link ms-3"><i class="bi bi-shield-lock"></i> Roles y Permisos</a>
            <?php endif; ?>
        </div>
    </div>
</aside>
<?php /**PATH /var/www/html/resources/views/partials/sidebar.blade.php ENDPATH**/ ?>