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
                <span>★ Principal</span>
                <span class="sidebar-chevron inline-block transition-transform duration-300" :class="{ 'rotate': open.principal }">▼</span>
            </button>
            <div 
                id="menuPrincipal"
                x-show="open.principal"
                x-transition
                class="sidebar-submenu"
            >
                @can('ver-lotes')
                <a href="{{ route('lotes.index') }}" class="sidebar-link">📍 Lotes</a>
                @endcan
                @can('ver-clientes')
                <a href="{{ route('clientes.index') }}" class="sidebar-link">👥 Clientes</a>
                @endcan
                @can('ver-proveedores')
                <a href="{{ route('proveedores.index') }}" class="sidebar-link">🚛 Proveedores</a>
                @endcan
                @can('ver-ventas')
                <a href="{{ route('ventas.index') }}" class="sidebar-link">🧾 Ventas</a>
                @endcan
                @can('ver-cargas')
                <a href="{{ route('cargas.index') }}" class="sidebar-link">📦 Cargas</a>
                @endcan
                @can('ver-choferes')
                <a href="{{ route('choferes.index') }}" class="sidebar-link">🪪 Choferes</a>
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
                <span>🔧 Recursos</span>
                <span class="sidebar-chevron inline-block transition-transform duration-300" :class="{ 'rotate': open.recursos }">▼</span>
            </button>
            <div 
                id="menuRecursos"
                x-show="open.recursos"
                x-transition
                class="sidebar-submenu"
            >
                @can('ver-insumos')
                <a href="{{ route('insumos.index') }}" class="sidebar-link">📦 Insumos</a>
                @endcan
                @can('ver-maquinarias')
                <a href="{{ route('maquinarias.index') }}" class="sidebar-link">🚛 Maquinarias</a>
                @endcan
                @can('ver-mantenimientos')
                <a href="{{ route('mantenimientos.index') }}" class="sidebar-link">🔧 Mantenimientos</a>
                @endcan
                @can('ver-kits-mantenimiento')
                <a href="{{ route('kits-mantenimiento.index') }}" class="sidebar-link">⚙️ Kits de Mantenimiento</a>
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
                <span>👥 Personal</span>
                <span class="sidebar-chevron inline-block transition-transform duration-300" :class="{ 'rotate': open.personal }">▼</span>
            </button>
            <div 
                id="menuPersonal"
                x-show="open.personal"
                x-transition
                class="sidebar-submenu"
            >
                @can('ver-empleados')
                <a href="{{ route('empleados.index') }}" class="sidebar-link">👷 Empleados</a>
                @endcan
                @can('ver-adelantos')
                <a href="{{ route('adelantos.index') }}" class="sidebar-link">💵 Adelantos</a>
                @endcan
                @can('ver-recibos')
                <a href="{{ route('recibos.index') }}" class="sidebar-link">📄 Recibos</a>
                @endcan
                @can('ver-liquidacion-pagos')
                <a href="{{ route('liquidacion-pagos.index') }}" class="sidebar-link">🧮 Liquidación de Pagos</a>
                @endcan
                @can('ver-asignaciones-lote')
                <a href="{{ route('asignaciones-lote.index') }}" class="sidebar-link">🔗 Asignaciones por Lote</a>
                @endcan
                @can('ver-propuestas-asignacion')
                <a href="{{ route('allocation-proposals.index') }}" class="sidebar-link">✨ Propuestas Automáticas</a>
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
                <span>📋 Operaciones</span>
                <span class="sidebar-chevron inline-block transition-transform duration-300" :class="{ 'rotate': open.operaciones }">▼</span>
            </button>
            <div 
                id="menuOperaciones"
                x-show="open.operaciones"
                x-transition
                class="sidebar-submenu"
            >
                @can('ver-partes-diarios')
                <a href="{{ route('partes-diarios.index') }}" class="sidebar-link">📋 Partes Diarios</a>
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
                <span>🕐 Históricos</span>
                <span class="sidebar-chevron inline-block transition-transform duration-300" :class="{ 'rotate': open.historicos }">▼</span>
            </button>
            <div 
                id="menuHistoricos"
                x-show="open.historicos"
                x-transition
                class="sidebar-submenu"
            >
                @can('ver-historico-costos-maquinarias')
                <a href="{{ route('historico-costos-maquinarias.index') }}" class="sidebar-link">📈 Costos Maquinarias</a>
                @endcan
                @can('ver-roles-laborales')
                <a href="{{ route('historico-roles-laborales.index') }}" class="sidebar-link">🪪 Roles Laborales</a>
                @endcan
                @can('ver-auditoria')
                <a href="{{ route('auditorias.index') }}" class="sidebar-link">📄 Auditorías</a>
                @endcan
                @can('ver-reportes')
                <a href="{{ route('reportes.estadisticas-forestales') }}" class="sidebar-link">📊 Estadísticas Forestales</a>
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
                <span>⚙️ Configuración</span>
                <span class="sidebar-chevron inline-block transition-transform duration-300" :class="{ 'rotate': open.configuracion }">▼</span>
            </button>
            <div 
                id="menuConfiguracion"
                x-show="open.configuracion"
                x-transition
                class="sidebar-submenu"
            >
                @can('ver-categorias-madera')
                <a href="{{ route('categorias-madera.index') }}" class="sidebar-link">🌲 Categorías Madera</a>
                @endcan
                @can('ver-lista-precios')
                <a href="{{ route('lista-precios.index') }}" class="sidebar-link">🏷️ Lista de Precios</a>
                @endcan
                @can('ver-unidades-medida')
                <a href="{{ route('unidades-medida.index') }}" class="sidebar-link">📏 Unidades de Medida</a>
                @endcan
                @can('ver-tipos-maquinaria')
                <a href="{{ route('tipos-maquinaria.index') }}" class="sidebar-link">⚙️ Tipos Maquinaria</a>
                @endcan
                @can('ver-roles-laborales')
                <a href="{{ route('roles-laborales.index') }}" class="sidebar-link">🪪 Roles Laborales</a>
                @endcan
                @can('gestionar-usuarios')
                <a href="{{ route('usuarios.index') }}" class="sidebar-link">👤 Usuarios</a>
                @endcan
                @can('gestionar-permisos')
                    <a href="{{ route('roles-permisos.index') }}" class="sidebar-link">🔒 Roles y Permisos</a>
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

    .sidebar-chevron {
        font-size: 0.75rem;
    }

    .sidebar-chevron.rotate {
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
</style>
