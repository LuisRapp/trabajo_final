<aside class="sidebar" x-data="sidebarState()" x-init="initSidebar()">
    <nav class="sidebar-nav" aria-label="Sidebar">
        <!-- Principal -->
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
                <a href="{{ route('lotes.index') }}" class="sidebar-link"><i class="bi bi-geo-alt"></i> Lotes</a>
                <a href="{{ route('clientes.index') }}" class="sidebar-link"><i class="bi bi-people"></i> Clientes</a>
                <a href="{{ route('proveedores.index') }}" class="sidebar-link"><i class="bi bi-truck"></i> Proveedores</a>
                <a href="{{ route('ventas.index') }}" class="sidebar-link"><i class="bi bi-receipt"></i> Ventas</a>
                <a href="{{ route('cargas.index') }}" class="sidebar-link"><i class="bi bi-box-seam"></i> Cargas</a>
                <a href="{{ route('choferes.index') }}" class="sidebar-link"><i class="bi bi-person-vcard"></i> Choferes</a>
            </div>
        </div>

        <!-- Recursos -->
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
                <a href="{{ route('insumos.index') }}" class="sidebar-link"><i class="bi bi-box-seam"></i> Insumos</a>
                <a href="{{ route('maquinarias.index') }}" class="sidebar-link"><i class="bi bi-truck"></i> Maquinarias</a>
                <a href="{{ route('mantenimientos.index') }}" class="sidebar-link"><i class="bi bi-tools"></i> Mantenimientos</a>
                <a href="{{ route('kits-mantenimiento.index') }}" class="sidebar-link"><i class="bi bi-gear-fill"></i> Kits de Mantenimiento</a>
            </div>
        </div>

        <!-- Personal -->
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
                <a href="{{ route('empleados.index') }}" class="sidebar-link"><i class="bi bi-person-workspace"></i> Empleados</a>
                <a href="{{ route('adelantos.index') }}" class="sidebar-link"><i class="bi bi-cash-coin"></i> Adelantos</a>
                <a href="{{ route('recibos.index') }}" class="sidebar-link"><i class="bi bi-file-earmark-text"></i> Recibos</a>
                <a href="{{ route('liquidacion-pagos.index') }}" class="sidebar-link"><i class="bi bi-calculator"></i> Liquidación de Pagos</a>
                <a href="{{ route('asignaciones-lote.index') }}" class="sidebar-link"><i class="bi bi-link-45deg"></i> Asignaciones por Lote</a>
            </div>
        </div>

        <!-- Operaciones -->
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
                <a href="{{ route('partes-diarios.index') }}" class="sidebar-link"><i class="bi bi-clipboard-check"></i> Partes Diarios</a>
            </div>
        </div>

        <!-- Históricos -->
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
                <a href="{{ route('historico-costos-maquinarias.index') }}" class="sidebar-link"><i class="bi bi-graph-up"></i> Costos Maquinarias</a>
                <a href="{{ route('historico-roles-laborales.index') }}" class="sidebar-link"><i class="bi bi-person-badge"></i> Roles Laborales</a>
                <a href="{{ route('auditorias.index') }}" class="sidebar-link"><i class="bi bi-file-earmark-text"></i> Auditorías</a>
            </div>
        </div>

        <!-- Configuración -->
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
                <a href="{{ route('categorias-madera.index') }}" class="sidebar-link"><i class="bi bi-tree"></i> Categorías Madera</a>
                <a href="{{ route('lista-precios.index') }}" class="sidebar-link"><i class="bi bi-tags"></i> Lista de Precios</a>
                <a href="{{ route('unidades-medida.index') }}" class="sidebar-link"><i class="bi bi-rulers"></i> Unidades de Medida</a>
                <a href="{{ route('tipos-maquinaria.index') }}" class="sidebar-link"><i class="bi bi-gear-wide-connected"></i> Tipos Maquinaria</a>
                <a href="{{ route('roles-laborales.index') }}" class="sidebar-link"><i class="bi bi-person-badge"></i> Roles Laborales</a>
                <a href="{{ route('usuarios.index') }}" class="sidebar-link"><i class="bi bi-person-circle"></i> Usuarios</a>
                @can('gestionar-permisos')
                    <a href="{{ route('roles-permisos.index') }}" class="sidebar-link"><i class="bi bi-shield-lock"></i> Roles y Permisos</a>
                @endcan
            </div>
        </div>
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
        padding: 0;
        overflow-y: auto;
    }

    .sidebar-menu-group {
        margin-bottom: 0;
    }

    .sidebar-menu-btn {
        width: 100%;
        padding: 0.5rem 0.75rem;
        border: none;
        background: transparent;
        color: #343A40;
        font-weight: 500;
        font-size: 0.85rem;
        text-align: left;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: space-between;
        transition: all 0.2s ease;
        border-left: 2px solid transparent;
    }

    .sidebar-menu-btn:hover {
        background-color: var(--bg-light);
        color: var(--primary-color);
        border-left-color: var(--primary-color);
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
        font-size: 0.8rem;
        width: auto;
        margin-right: 0;
    }

    .sidebar-menu-btn i:last-child.rotate {
        transform: rotate(180deg);
    }

    .sidebar-submenu {
        padding: 0;
        background: transparent;
        border-left: none;
        margin-left: 0;
    }

    .sidebar-link {
        display: flex;
        align-items: center;
        padding: 0.5rem 0.75rem;
        color: #343A40;
        text-decoration: none;
        font-size: 0.85rem;
        transition: all 0.2s;
        border-left: 2px solid transparent;
    }

    .sidebar-link:hover {
        background: var(--bg-light);
        color: var(--primary-color);
        border-left-color: var(--primary-color);
        padding-left: 0.9rem;
    }

    .sidebar-link i {
        margin-right: 0.4rem;
        width: 16px;
        text-align: center;
        font-size: 0.9rem;
    }
</style>
