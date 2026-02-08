<aside class="sidebar" x-data="sidebarState()" x-init="initSidebar()">
    @php
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
    @endphp
    <nav class="sidebar-nav" aria-label="Sidebar">
        <!-- Principal -->
        @if($canPrincipal)
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
                @can('ver-lotes')
                <a href="{{ route('lotes.index') }}" class="sidebar-link"><i class="bi bi-geo-alt"></i> Lotes</a>
                @endcan
                @can('ver-clientes')
                <a href="{{ route('clientes.index') }}" class="sidebar-link"><i class="bi bi-people"></i> Clientes</a>
                @endcan
                @can('ver-proveedores')
                <a href="{{ route('proveedores.index') }}" class="sidebar-link"><i class="bi bi-truck"></i> Proveedores</a>
                @endcan
                @can('ver-ventas')
                <a href="{{ route('ventas.index') }}" class="sidebar-link"><i class="bi bi-receipt"></i> Ventas</a>
                @endcan
                @can('ver-cargas')
                <a href="{{ route('cargas.index') }}" class="sidebar-link"><i class="bi bi-box-seam"></i> Cargas</a>
                @endcan
                @can('ver-choferes')
                <a href="{{ route('choferes.index') }}" class="sidebar-link"><i class="bi bi-person-vcard"></i> Choferes</a>
                @endcan
            </div>
        </div>
        @endif

        <!-- Recursos -->
        @if($canRecursos)
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
                @can('ver-insumos')
                <a href="{{ route('insumos.index') }}" class="sidebar-link"><i class="bi bi-box-seam"></i> Insumos</a>
                @endcan
                @can('ver-maquinarias')
                <a href="{{ route('maquinarias.index') }}" class="sidebar-link"><i class="bi bi-truck"></i> Maquinarias</a>
                @endcan
                @can('ver-mantenimientos')
                <a href="{{ route('mantenimientos.index') }}" class="sidebar-link"><i class="bi bi-tools"></i> Mantenimientos</a>
                @endcan
                @can('ver-kits-mantenimiento')
                <a href="{{ route('kits-mantenimiento.index') }}" class="sidebar-link"><i class="bi bi-gear-fill"></i> Kits de Mantenimiento</a>
                @endcan
            </div>
        </div>
        @endif

        <!-- Personal -->
        @if($canPersonal)
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
                @can('ver-empleados')
                <a href="{{ route('empleados.index') }}" class="sidebar-link"><i class="bi bi-person-workspace"></i> Empleados</a>
                @endcan
                @can('ver-adelantos')
                <a href="{{ route('adelantos.index') }}" class="sidebar-link"><i class="bi bi-cash-coin"></i> Adelantos</a>
                @endcan
                @can('ver-recibos')
                <a href="{{ route('recibos.index') }}" class="sidebar-link"><i class="bi bi-file-earmark-text"></i> Recibos</a>
                @endcan
                @can('ver-liquidacion-pagos')
                <a href="{{ route('liquidacion-pagos.index') }}" class="sidebar-link"><i class="bi bi-calculator"></i> Liquidación de Pagos</a>
                @endcan
                @can('ver-asignaciones-lote')
                <a href="{{ route('asignaciones-lote.index') }}" class="sidebar-link"><i class="bi bi-link-45deg"></i> Asignaciones por Lote</a>
                @endcan
                @can('ver-propuestas-asignacion')
                <a href="{{ route('allocation-proposals.index') }}" class="sidebar-link"><i class="bi bi-magic"></i> Propuestas Automáticas</a>
                @endcan
            </div>
        </div>
        @endif

        <!-- Operaciones -->
        @if($canOperaciones)
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
                @can('ver-partes-diarios')
                <a href="{{ route('partes-diarios.index') }}" class="sidebar-link"><i class="bi bi-clipboard-check"></i> Partes Diarios</a>
                @endcan
            </div>
        </div>
        @endif

        <!-- Históricos -->
        @if($canHistoricos)
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
                @can('ver-historico-costos-maquinarias')
                <a href="{{ route('historico-costos-maquinarias.index') }}" class="sidebar-link"><i class="bi bi-graph-up"></i> Costos Maquinarias</a>
                @endcan
                @can('ver-roles-laborales')
                <a href="{{ route('historico-roles-laborales.index') }}" class="sidebar-link"><i class="bi bi-person-badge"></i> Roles Laborales</a>
                @endcan
                @can('ver-auditoria')
                <a href="{{ route('auditorias.index') }}" class="sidebar-link"><i class="bi bi-file-earmark-text"></i> Auditorías</a>
                @endcan
                @can('ver-reportes')
                <a href="{{ route('reportes.estadisticas-forestales') }}" class="sidebar-link"><i class="bi bi-bar-chart"></i> Estadísticas Forestales</a>
                @endcan
            </div>
        </div>
        @endif

        <!-- Configuración -->
        @if($canConfiguracion)
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
                @can('ver-categorias-madera')
                <a href="{{ route('categorias-madera.index') }}" class="sidebar-link"><i class="bi bi-tree"></i> Categorías Madera</a>
                @endcan
                @can('ver-lista-precios')
                <a href="{{ route('lista-precios.index') }}" class="sidebar-link"><i class="bi bi-tags"></i> Lista de Precios</a>
                @endcan
                @can('ver-unidades-medida')
                <a href="{{ route('unidades-medida.index') }}" class="sidebar-link"><i class="bi bi-rulers"></i> Unidades de Medida</a>
                @endcan
                @can('ver-tipos-maquinaria')
                <a href="{{ route('tipos-maquinaria.index') }}" class="sidebar-link"><i class="bi bi-gear-wide-connected"></i> Tipos Maquinaria</a>
                @endcan
                @can('ver-roles-laborales')
                <a href="{{ route('roles-laborales.index') }}" class="sidebar-link"><i class="bi bi-person-badge"></i> Roles Laborales</a>
                @endcan
                @can('gestionar-usuarios')
                <a href="{{ route('usuarios.index') }}" class="sidebar-link"><i class="bi bi-person-circle"></i> Usuarios</a>
                @endcan
                @can('gestionar-permisos')
                    <a href="{{ route('roles-permisos.index') }}" class="sidebar-link"><i class="bi bi-shield-lock"></i> Roles y Permisos</a>
                @endcan
            </div>
        </div>
        @endif
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
