<aside class="sidebar" x-data="sidebarState()" x-init="initSidebar()">
    <?php
        $user = auth()->user();
        $canPrincipal = $user && (
            $user->can('ver-lotes')
            || $user->can('ver-clientes')
            || $user->can('ver-proveedores')
            || $user->can('ver-ventas')
            || $user->can('ver-cargas')
            || $user->can('ver-choferes')
        );
        $canRecursos = $user && (
            $user->can('ver-insumos')
            || $user->can('ver-maquinarias')
            || $user->can('ver-mantenimientos')
            || $user->can('ver-kits-mantenimiento')
        );
        $canPersonal = $user && (
            $user->can('ver-empleados')
            || $user->can('ver-adelantos')
            || $user->can('ver-recibos')
            || $user->can('ver-liquidacion-pagos')
            || $user->can('ver-asignaciones-lote')
            || $user->can('ver-propuestas-asignacion')
            || $user->can('ver-roles-laborales')
        );
        $canOperaciones = $user && (
            $user->can('ver-partes-diarios')
        );
        $canHistoricos = $user && (
            $user->can('ver-historico-costos-maquinarias')
            || $user->can('ver-roles-laborales')
            || $user->can('ver-auditoria')
            || $user->can('ver-reportes')
        );
        $canConfiguracion = $user && (
            $user->can('ver-categorias-madera')
            || $user->can('ver-lista-precios')
            || $user->can('ver-unidades-medida')
            || $user->can('ver-tipos-maquinaria')
            || $user->can('ver-roles-laborales')
            || $user->can('gestionar-usuarios')
            || $user->can('gestionar-permisos')
        );
    ?>
    <nav class="sidebar-nav" aria-label="Sidebar">
        <!-- Principal -->
        <?php if($canPrincipal): ?>
        <div class="sidebar-menu-group">
            <button 
                type="button"
                @click="toggle('principal')"
                :aria-expanded="open.principal"
                class="sidebar-menu-btn"
                aria-controls="menuPrincipal"
            >
                <span><i class="bi bi-star-fill"></i> Principal</span>
                <i class="bi bi-chevron-down" :class="{ 'rotate': open.principal }"></i>
            </button>
            <div 
                id="menuPrincipal"
                x-show="open.principal"
                x-transition
                class="sidebar-submenu"
            >
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('ver-lotes')): ?>
                <a href="<?php echo e(route('lotes.index')); ?>" class="sidebar-link"><i class="bi bi-geo-alt"></i> Lotes</a>
                <?php endif; ?>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('ver-clientes')): ?>
                <a href="<?php echo e(route('clientes.index')); ?>" class="sidebar-link"><i class="bi bi-people"></i> Clientes</a>
                <?php endif; ?>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('ver-proveedores')): ?>
                <a href="<?php echo e(route('proveedores.index')); ?>" class="sidebar-link"><i class="bi bi-truck"></i> Proveedores</a>
                <?php endif; ?>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('ver-ventas')): ?>
                <a href="<?php echo e(route('ventas.index')); ?>" class="sidebar-link"><i class="bi bi-receipt"></i> Ventas</a>
                <?php endif; ?>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('ver-cargas')): ?>
                <a href="<?php echo e(route('cargas.index')); ?>" class="sidebar-link"><i class="bi bi-box-seam"></i> Cargas</a>
                <?php endif; ?>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('ver-choferes')): ?>
                <a href="<?php echo e(route('choferes.index')); ?>" class="sidebar-link"><i class="bi bi-person-vcard"></i> Choferes</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Recursos -->
        <?php if($canRecursos): ?>
        <div class="sidebar-menu-group">
            <button 
                type="button"
                @click="toggle('recursos')"
                :aria-expanded="open.recursos"
                class="sidebar-menu-btn"
                aria-controls="menuRecursos"
            >
                <span><i class="bi bi-tools"></i> Recursos</span>
                <i class="bi bi-chevron-down" :class="{ 'rotate': open.recursos }"></i>
            </button>
            <div 
                id="menuRecursos"
                x-show="open.recursos"
                x-transition
                class="sidebar-submenu"
            >
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('ver-insumos')): ?>
                <a href="<?php echo e(route('insumos.index')); ?>" class="sidebar-link"><i class="bi bi-box-seam"></i> Insumos</a>
                <?php endif; ?>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('ver-maquinarias')): ?>
                <a href="<?php echo e(route('maquinarias.index')); ?>" class="sidebar-link"><i class="bi bi-truck"></i> Maquinarias</a>
                <?php endif; ?>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('ver-mantenimientos')): ?>
                <a href="<?php echo e(route('mantenimientos.index')); ?>" class="sidebar-link"><i class="bi bi-tools"></i> Mantenimientos</a>
                <?php endif; ?>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('ver-kits-mantenimiento')): ?>
                <a href="<?php echo e(route('kits-mantenimiento.index')); ?>" class="sidebar-link"><i class="bi bi-gear-fill"></i> Kits de Mantenimiento</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Personal -->
        <?php if($canPersonal): ?>
        <div class="sidebar-menu-group">
            <button 
                type="button"
                @click="toggle('personal')"
                :aria-expanded="open.personal"
                class="sidebar-menu-btn"
                aria-controls="menuPersonal"
            >
                <span><i class="bi bi-people-fill"></i> Personal</span>
                <i class="bi bi-chevron-down" :class="{ 'rotate': open.personal }"></i>
            </button>
            <div 
                id="menuPersonal"
                x-show="open.personal"
                x-transition
                class="sidebar-submenu"
            >
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('ver-empleados')): ?>
                <a href="<?php echo e(route('empleados.index')); ?>" class="sidebar-link"><i class="bi bi-person-workspace"></i> Empleados</a>
                <?php endif; ?>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('ver-adelantos')): ?>
                <a href="<?php echo e(route('adelantos.index')); ?>" class="sidebar-link"><i class="bi bi-cash-coin"></i> Adelantos</a>
                <?php endif; ?>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('ver-recibos')): ?>
                <a href="<?php echo e(route('recibos.index')); ?>" class="sidebar-link"><i class="bi bi-file-earmark-text"></i> Recibos</a>
                <?php endif; ?>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('ver-liquidacion-pagos')): ?>
                <a href="<?php echo e(route('liquidacion-pagos.index')); ?>" class="sidebar-link"><i class="bi bi-calculator"></i> Liquidación de Pagos</a>
                <?php endif; ?>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('ver-asignaciones-lote')): ?>
                <a href="<?php echo e(route('asignaciones-lote.index')); ?>" class="sidebar-link"><i class="bi bi-link-45deg"></i> Asignaciones por Lote</a>
                <?php endif; ?>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('ver-propuestas-asignacion')): ?>
                <a href="<?php echo e(route('allocation-proposals.index')); ?>" class="sidebar-link"><i class="bi bi-magic"></i> Propuestas Automáticas</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Operaciones -->
        <?php if($canOperaciones): ?>
        <div class="sidebar-menu-group">
            <button 
                type="button"
                @click="toggle('operaciones')"
                :aria-expanded="open.operaciones"
                class="sidebar-menu-btn"
                aria-controls="menuOperaciones"
            >
                <span><i class="bi bi-clipboard-check"></i> Operaciones</span>
                <i class="bi bi-chevron-down" :class="{ 'rotate': open.operaciones }"></i>
            </button>
            <div 
                id="menuOperaciones"
                x-show="open.operaciones"
                x-transition
                class="sidebar-submenu"
            >
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('ver-partes-diarios')): ?>
                <a href="<?php echo e(route('partes-diarios.index')); ?>" class="sidebar-link"><i class="bi bi-clipboard-check"></i> Partes Diarios</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Históricos -->
        <?php if($canHistoricos): ?>
        <div class="sidebar-menu-group">
            <button 
                type="button"
                @click="toggle('historicos')"
                :aria-expanded="open.historicos"
                class="sidebar-menu-btn"
                aria-controls="menuHistoricos"
            >
                <span><i class="bi bi-clock-history"></i> Históricos</span>
                <i class="bi bi-chevron-down" :class="{ 'rotate': open.historicos }"></i>
            </button>
            <div 
                id="menuHistoricos"
                x-show="open.historicos"
                x-transition
                class="sidebar-submenu"
            >
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('ver-historico-costos-maquinarias')): ?>
                <a href="<?php echo e(route('historico-costos-maquinarias.index')); ?>" class="sidebar-link"><i class="bi bi-graph-up"></i> Costos Maquinarias</a>
                <?php endif; ?>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('ver-roles-laborales')): ?>
                <a href="<?php echo e(route('historico-roles-laborales.index')); ?>" class="sidebar-link"><i class="bi bi-person-badge"></i> Roles Laborales</a>
                <?php endif; ?>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('ver-auditoria')): ?>
                <a href="<?php echo e(route('auditorias.index')); ?>" class="sidebar-link"><i class="bi bi-file-earmark-text"></i> Auditorías</a>
                <?php endif; ?>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('ver-reportes')): ?>
                <a href="<?php echo e(route('reportes.estadisticas-forestales')); ?>" class="sidebar-link"><i class="bi bi-bar-chart"></i> Estadísticas Forestales</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Configuración -->
        <?php if($canConfiguracion): ?>
        <div class="sidebar-menu-group">
            <button 
                type="button"
                @click="toggle('configuracion')"
                :aria-expanded="open.configuracion"
                class="sidebar-menu-btn"
                aria-controls="menuConfiguracion"
            >
                <span><i class="bi bi-gear-fill"></i> Configuración</span>
                <i class="bi bi-chevron-down" :class="{ 'rotate': open.configuracion }"></i>
            </button>
            <div 
                id="menuConfiguracion"
                x-show="open.configuracion"
                x-transition
                class="sidebar-submenu"
            >
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('ver-categorias-madera')): ?>
                <a href="<?php echo e(route('categorias-madera.index')); ?>" class="sidebar-link"><i class="bi bi-tree"></i> Categorías Madera</a>
                <?php endif; ?>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('ver-lista-precios')): ?>
                <a href="<?php echo e(route('lista-precios.index')); ?>" class="sidebar-link"><i class="bi bi-tags"></i> Lista de Precios</a>
                <?php endif; ?>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('ver-unidades-medida')): ?>
                <a href="<?php echo e(route('unidades-medida.index')); ?>" class="sidebar-link"><i class="bi bi-rulers"></i> Unidades de Medida</a>
                <?php endif; ?>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('ver-tipos-maquinaria')): ?>
                <a href="<?php echo e(route('tipos-maquinaria.index')); ?>" class="sidebar-link"><i class="bi bi-gear-wide-connected"></i> Tipos Maquinaria</a>
                <?php endif; ?>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('ver-roles-laborales')): ?>
                <a href="<?php echo e(route('roles-laborales.index')); ?>" class="sidebar-link"><i class="bi bi-person-badge"></i> Roles Laborales</a>
                <?php endif; ?>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('gestionar-usuarios')): ?>
                <a href="<?php echo e(route('usuarios.index')); ?>" class="sidebar-link"><i class="bi bi-person-circle"></i> Usuarios</a>
                <?php endif; ?>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('gestionar-permisos')): ?>
                    <a href="<?php echo e(route('roles-permisos.index')); ?>" class="sidebar-link"><i class="bi bi-shield-lock"></i> Roles y Permisos</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </nav>
</aside>

<script>
    function sidebarState() {
        return {
            open: {
                principal: true,
                recursos: false,
                personal: false,
                operaciones: false,
                historicos: false,
                configuracion: false,
            },
            toggle(menu) {
                this.open[menu] = !this.open[menu];
                this.saveSidebarState();
            },
            saveSidebarState() {
                localStorage.setItem('sidebarState', JSON.stringify(this.open));
            },
            loadSidebarState() {
                const saved = localStorage.getItem('sidebarState');
                if (saved) {
                    this.open = JSON.parse(saved);
                }
            },
            initSidebar() {
                this.loadSidebarState();
            }
        }
    }
</script>

<style>
    .sidebar {
        display: flex;
        flex-direction: column;
        height: 100%;
    }

    .sidebar-nav {
        flex: 1;
        padding: 0.5rem 0.25rem;
        overflow-y: auto;
    }

    .sidebar-menu-group {
        margin-bottom: 0.25rem;
    }

    .sidebar-menu-btn {
        width: 100%;
        padding: 0.5rem 0.75rem;
        border: none;
        background: transparent;
        color: #0f172a;
        font-weight: 600;
        font-size: 0.8rem;
        text-align: left;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: space-between;
        transition: all 0.2s ease;
        border-left: 2px solid transparent;
    }

    .sidebar-menu-btn:hover {
        background-color: #f1f5f9;
        color: #0f172a;
        border-left-color: #94a3b8;
        padding-left: 0.9rem;
    }

    .sidebar-menu-btn i:first-child {
        margin-right: 0.5rem;
        width: 16px;
        font-size: 0.9rem;
        text-align: center;
    }

    .sidebar-menu-btn i:last-child {
        transition: transform 0.3s ease;
        font-size: 0.75rem;
        width: auto;
        margin-right: 0;
    }

    .sidebar-menu-btn i:last-child.rotate {
        transform: rotate(180deg);
    }

    .sidebar-submenu {
        padding: 0.25rem 0.25rem 0.5rem 0.25rem;
        background: transparent;
        border-left: none;
        margin-left: 0;
    }

    .sidebar-link {
        display: flex;
        align-items: center;
        padding: 0.45rem 0.75rem;
        color: #334155;
        text-decoration: none;
        transition: all 0.2s;
        border-left: 2px solid transparent;
        font-size: 0.8rem;
        border-radius: 0.375rem;
    }

    .sidebar-link:hover {
        background-color: #f1f5f9;
        color: #0f172a;
        border-left-color: #94a3b8;
        padding-left: 0.9rem;
    }

    .sidebar-link i {
        margin-right: 0.5rem;
        width: 16px;
        font-size: 0.9rem;
        text-align: center;
        color: #64748b;
    }
</style>
<?php /**PATH D:\trabajo_final\rennova\resources\views/partials/sidebar.blade.php ENDPATH**/ ?>