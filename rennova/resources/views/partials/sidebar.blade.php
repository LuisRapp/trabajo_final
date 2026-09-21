<aside
    class="sidebar"
    :class="{ 'collapsed': collapsed, 'show': mobileOpen }"
    x-data="sidebarState()"
    x-init="initSidebar()"
    id="sidebar"
>
    @php
        $user = auth()->user();

        $canOperacionesForestales = $user && (
            $user->can('ver-lotes')
            || $user->can('ver-partes-diarios')
            || $user->can('ver-cargas')
            || $user->can('ver-ventas')
            || $user->can('ver-asignaciones-lote')
            || $user->can('ver-propuestas-asignacion')
            || $user->can('ver-clientes')
        );

        $canInventarioAbastecimiento = $user && (
            $user->can('ver-gestion-stock')
            || $user->can('ver-insumos')
            || $user->can('ver-proveedores')
        );

        $canMaquinaria = $user && (
            $user->can('ver-maquinarias')
            || $user->can('ver-mantenimientos')
            || $user->can('ver-kits-mantenimiento')
            || $user->can('ver-historico-costos-maquinarias')
            || $user->can('configurar-notificaciones-mantenimiento')
        );

        $canPersonalPagos = $user && (
            $user->can('ver-empleados')
            || $user->can('ver-choferes')
            || $user->can('ver-adelantos')
            || $user->can('ver-recibos')
            || $user->can('ver-liquidacion-pagos')
        );

        $canReportesAuditoria = $user && (
            $user->can('ver-reportes')
            || $user->can('ver-auditoria')
        );

        $canAdministracion = $user && (
            $user->can('gestionar-usuarios')
            || $user->can('gestionar-permisos')
            || $user->can('configurar-mantenimiento')
            || $user->can('configurar-notificaciones-mantenimiento')
            || $user->can('ver-categorias-madera')
            || $user->can('ver-unidades-medida')
            || $user->can('ver-lista-precios')
            || $user->can('ver-tipos-maquinaria')
            || $user->can('ver-roles-laborales')
        );

        $esActiva = fn(...$patrones) => request()->routeIs(...$patrones);
    @endphp

    <nav class="sidebar-nav" aria-label="Sidebar">
        <!-- Dashboard -->
        <div class="sidebar-menu-group">
            <a href="{{ route('dashboard') }}" class="sidebar-link {{ $esActiva('dashboard') ? 'active' : '' }}">
                <span class="inline-flex items-center gap-1.5">
                    <flux:icon.home class="size-3.5" /> Dashboard
                </span>
            </a>
        </div>

        <!-- Operaciones forestales -->
        @if($canOperacionesForestales)
        <div class="sidebar-menu-group">
            <button
                type="button"
                @click="toggle('operaciones-forestales')"
                :aria-expanded="open['operaciones-forestales']"
                class="sidebar-menu-btn"
                aria-controls="menuOperacionesForestales"
            >
                <span class="inline-flex items-center gap-1.5"><flux:icon.map class="size-3.5" /> Operaciones forestales</span>
                <span class="text-xs transition-transform duration-300 inline-block" :class="{ 'rotate-180': open['operaciones-forestales'] }"><flux:icon.chevron-down class="size-3" /></span>
            </button>
            <div
                id="menuOperacionesForestales"
                x-show="open['operaciones-forestales']"
                x-transition
                class="sidebar-submenu"
            >
                @can('ver-lotes')
                <a href="{{ route('lotes.index') }}" class="sidebar-link {{ $esActiva('lotes.*') ? 'active' : '' }}"><span class="inline-flex items-center gap-1.5"><flux:icon.map-pin class="size-3.5" /> Lotes</span></a>
                @endcan
                @can('ver-partes-diarios')
                <a href="{{ route('partes-diarios.index') }}" class="sidebar-link {{ $esActiva('partes-diarios.*') ? 'active' : '' }}"><span class="inline-flex items-center gap-1.5"><flux:icon.clipboard-document-list class="size-3.5" /> Partes diarios</span></a>
                @endcan
                @can('ver-cargas')
                <a href="{{ route('cargas.index') }}" class="sidebar-link {{ $esActiva('cargas.*') ? 'active' : '' }}"><span class="inline-flex items-center gap-1.5"><flux:icon.truck class="size-3.5" /> Cargas</span></a>
                @endcan
                @can('ver-ventas')
                <a href="{{ route('ventas.index') }}" class="sidebar-link {{ $esActiva('ventas.*') ? 'active' : '' }}"><span class="inline-flex items-center gap-1.5"><flux:icon.document-text class="size-3.5" /> Ventas</span></a>
                @endcan
                @can('ver-clientes')
                <a href="{{ route('clientes.index') }}" class="sidebar-link {{ $esActiva('clientes.*') ? 'active' : '' }}"><span class="inline-flex items-center gap-1.5"><flux:icon.users class="size-3.5" /> Clientes</span></a>
                @endcan
                @can('ver-asignaciones-lote')
                <a href="{{ route('asignaciones-lote.index') }}" class="sidebar-link {{ $esActiva('asignaciones-lote.*', 'propuestas-asignacion.*') ? 'active' : '' }}"><span class="inline-flex items-center gap-1.5"><flux:icon.link class="size-3.5" /> Asignaciones por lote</span></a>
                @endcan
            </div>
        </div>
        @endif

        <!-- Inventario y abastecimiento -->
        @if($canInventarioAbastecimiento)
        <div class="sidebar-menu-group">
            <button
                type="button"
                @click="toggle('inventario-abastecimiento')"
                :aria-expanded="open['inventario-abastecimiento']"
                class="sidebar-menu-btn"
                aria-controls="menuInventarioAbastecimiento"
            >
                <span class="inline-flex items-center gap-1.5"><flux:icon.archive-box class="size-3.5" /> Inventario y abastecimiento</span>
                <span class="text-xs transition-transform duration-300 inline-block" :class="{ 'rotate-180': open['inventario-abastecimiento'] }"><flux:icon.chevron-down class="size-3" /></span>
            </button>
            <div
                id="menuInventarioAbastecimiento"
                x-show="open['inventario-abastecimiento']"
                x-transition
                class="sidebar-submenu"
            >
                @can('ver-gestion-stock')
                <a href="{{ route('modulos.operaciones.gestionstock') }}" class="sidebar-link {{ $esActiva('modulos.operaciones.gestionstock') ? 'active' : '' }}"><span class="inline-flex items-center gap-1.5"><flux:icon.square-3-stack-3d class="size-3.5" /> Stock</span></a>
                @endcan
                @can('ver-insumos')
                <a href="{{ route('insumos.index') }}" class="sidebar-link {{ $esActiva('insumos.*') ? 'active' : '' }}"><span class="inline-flex items-center gap-1.5"><flux:icon.cube class="size-3.5" /> Insumos</span></a>
                @endcan
                @can('ver-proveedores')
                <a href="{{ route('proveedores.index') }}" class="sidebar-link {{ $esActiva('proveedores.*') ? 'active' : '' }}"><span class="inline-flex items-center gap-1.5"><flux:icon.truck class="size-3.5" /> Proveedores</span></a>
                @endcan
            </div>
        </div>
        @endif

        <!-- Maquinaria -->
        @if($canMaquinaria)
        <div class="sidebar-menu-group">
            <button
                type="button"
                @click="toggle('maquinaria')"
                :aria-expanded="open.maquinaria"
                class="sidebar-menu-btn"
                aria-controls="menuMaquinaria"
            >
                <span class="inline-flex items-center gap-1.5"><flux:icon.wrench-screwdriver class="size-3.5" /> Maquinaria</span>
                <span class="text-xs transition-transform duration-300 inline-block" :class="{ 'rotate-180': open.maquinaria }"><flux:icon.chevron-down class="size-3" /></span>
            </button>
            <div
                id="menuMaquinaria"
                x-show="open.maquinaria"
                x-transition
                class="sidebar-submenu"
            >
                @can('ver-maquinarias')
                <a href="{{ route('maquinarias.index') }}" class="sidebar-link {{ $esActiva('maquinarias.*') ? 'active' : '' }}"><span class="inline-flex items-center gap-1.5"><flux:icon.truck class="size-3.5" /> Maquinarias</span></a>
                @endcan
                @can('ver-mantenimientos')
                <a href="{{ route('mantenimientos.index') }}" class="sidebar-link {{ $esActiva('mantenimientos.*') ? 'active' : '' }}"><span class="inline-flex items-center gap-1.5"><flux:icon.wrench class="size-3.5" /> Mantenimientos</span></a>
                @endcan
                @can('ver-kits-mantenimiento')
                <a href="{{ route('kits-mantenimiento.index') }}" class="sidebar-link {{ $esActiva('kits-mantenimiento.*') ? 'active' : '' }}"><span class="inline-flex items-center gap-1.5"><flux:icon.cog class="size-3.5" /> Kits de mantenimiento</span></a>
                @endcan
                @can('ver-mantenimientos')
                <a href="{{ route('programar-mantenimiento') }}" class="sidebar-link {{ $esActiva('programar-mantenimiento') ? 'active' : '' }}"><span class="inline-flex items-center gap-1.5"><flux:icon.calendar-date-range class="size-3.5" /> Programar mantenimiento</span></a>
                @endcan
                @can('ver-historico-costos-maquinarias')
                <a href="{{ route('historico-costos-maquinarias.index') }}" class="sidebar-link {{ $esActiva('historico-costos-maquinarias.*') ? 'active' : '' }}"><span class="inline-flex items-center gap-1.5"><flux:icon.chart-bar class="size-3.5" /> Histórico de costos</span></a>
                @endcan
            </div>
        </div>
        @endif

        <!-- Personal y pagos -->
        @if($canPersonalPagos)
        <div class="sidebar-menu-group">
            <button
                type="button"
                @click="toggle('personal-pagos')"
                :aria-expanded="open['personal-pagos']"
                class="sidebar-menu-btn"
                aria-controls="menuPersonalPagos"
            >
                <span class="inline-flex items-center gap-1.5"><flux:icon.users class="size-3.5" /> Personal y pagos</span>
                <span class="text-xs transition-transform duration-300 inline-block" :class="{ 'rotate-180': open['personal-pagos'] }"><flux:icon.chevron-down class="size-3" /></span>
            </button>
            <div
                id="menuPersonalPagos"
                x-show="open['personal-pagos']"
                x-transition
                class="sidebar-submenu"
            >
                @can('ver-empleados')
                <a href="{{ route('empleados.index') }}" class="sidebar-link {{ $esActiva('empleados.*') ? 'active' : '' }}"><span class="inline-flex items-center gap-1.5"><flux:icon.user class="size-3.5" /> Empleados</span></a>
                @endcan
                @can('ver-choferes')
                <a href="{{ route('choferes.index') }}" class="sidebar-link {{ $esActiva('choferes.*') ? 'active' : '' }}"><span class="inline-flex items-center gap-1.5"><flux:icon.identification class="size-3.5" /> Choferes</span></a>
                @endcan
                @can('ver-adelantos')
                <a href="{{ route('adelantos.index') }}" class="sidebar-link {{ $esActiva('adelantos.*') ? 'active' : '' }}"><span class="inline-flex items-center gap-1.5"><flux:icon.banknotes class="size-3.5" /> Adelantos</span></a>
                @endcan
                @can('ver-recibos')
                <a href="{{ route('recibos.index') }}" class="sidebar-link {{ $esActiva('recibos.*') ? 'active' : '' }}"><span class="inline-flex items-center gap-1.5"><flux:icon.document-text class="size-3.5" /> Recibos</span></a>
                @endcan
                @can('ver-liquidacion-pagos')
                <a href="{{ route('liquidacion-pagos.index') }}" class="sidebar-link {{ $esActiva('liquidacion-pagos.*') ? 'active' : '' }}"><span class="inline-flex items-center gap-1.5"><flux:icon.calculator class="size-3.5" /> Liquidación de pagos</span></a>
                @endcan
                @can('ver-roles-laborales')
                <a href="{{ route('historico-roles-laborales.index') }}" class="sidebar-link {{ $esActiva('historico-roles-laborales.*') ? 'active' : '' }}"><span class="inline-flex items-center gap-1.5"><flux:icon.clock class="size-3.5" /> Histórico de roles</span></a>
                @endcan
            </div>
        </div>
        @endif

        <!-- Reportes y auditoría -->
        @if($canReportesAuditoria)
        <div class="sidebar-menu-group">
            <button
                type="button"
                @click="toggle('reportes-auditoria')"
                :aria-expanded="open['reportes-auditoria']"
                class="sidebar-menu-btn"
                aria-controls="menuReportesAuditoria"
            >
                <span class="inline-flex items-center gap-1.5"><flux:icon.chart-pie class="size-3.5" /> Reportes y auditoría</span>
                <span class="text-xs transition-transform duration-300 inline-block" :class="{ 'rotate-180': open['reportes-auditoria'] }"><flux:icon.chevron-down class="size-3" /></span>
            </button>
            <div
                id="menuReportesAuditoria"
                x-show="open['reportes-auditoria']"
                x-transition
                class="sidebar-submenu"
            >
                @can('ver-reportes')
                <a href="{{ route('reportes.estadisticas-forestales') }}" class="sidebar-link {{ $esActiva('reportes.estadisticas-forestales') ? 'active' : '' }}"><span class="inline-flex items-center gap-1.5"><flux:icon.chart-bar class="size-3.5" /> Estadísticas forestales</span></a>
                @endcan
                @can('ver-auditoria')
                <a href="{{ route('auditorias.index') }}" class="sidebar-link {{ $esActiva('auditorias.*') ? 'active' : '' }}"><span class="inline-flex items-center gap-1.5"><flux:icon.shield-check class="size-3.5" /> Auditorías</span></a>
                @endcan
            </div>
        </div>
        @endif

        <!-- Administración -->
        @if($canAdministracion)
        <div class="sidebar-menu-group">
            <button
                type="button"
                @click="toggle('administracion')"
                :aria-expanded="open.administracion"
                class="sidebar-menu-btn"
                aria-controls="menuAdministracion"
            >
                <span class="inline-flex items-center gap-1.5"><flux:icon.cog class="size-3.5" /> Administración</span>
                <span class="text-xs transition-transform duration-300 inline-block" :class="{ 'rotate-180': open.administracion }"><flux:icon.chevron-down class="size-3" /></span>
            </button>
            <div
                id="menuAdministracion"
                x-show="open.administracion"
                x-transition
                class="sidebar-submenu"
            >
                @can('gestionar-usuarios')
                <a href="{{ route('usuarios.index') }}" class="sidebar-link {{ $esActiva('usuarios.*') ? 'active' : '' }}"><span class="inline-flex items-center gap-1.5"><flux:icon.user-group class="size-3.5" /> Usuarios</span></a>
                @endcan
                @can('gestionar-usuarios')
                <a href="{{ route('estado-procesos.index') }}" class="sidebar-link {{ $esActiva('estado-procesos.*') ? 'active' : '' }}"><span class="inline-flex items-center gap-1.5"><flux:icon.queue-list class="size-3.5" /> Estado de procesos</span></a>
                @endcan
                @can('gestionar-permisos')
                <a href="{{ route('roles-permisos.index') }}" class="sidebar-link {{ $esActiva('roles-permisos.*') ? 'active' : '' }}"><span class="inline-flex items-center gap-1.5"><flux:icon.lock-closed class="size-3.5" /> Roles y permisos</span></a>
                @endcan
                @can('configurar-mantenimiento')
                <a href="{{ route('configuracion-mantenimiento.index') }}" class="sidebar-link {{ $esActiva('configuracion-mantenimiento.*') ? 'active' : '' }}"><span class="inline-flex items-center gap-1.5"><flux:icon.calendar-date-range class="size-3.5" /> Configuración de mantenimiento</span></a>
                @endcan
                @can('configurar-notificaciones-mantenimiento')
                <a href="{{ route('configuracion-notificaciones.index') }}" class="sidebar-link {{ $esActiva('configuracion-notificaciones.*') ? 'active' : '' }}"><span class="inline-flex items-center gap-1.5"><flux:icon.bell class="size-3.5" /> Configuración de notificaciones</span></a>
                @endcan
                @can('ver-categorias-madera')
                <a href="{{ route('categorias-madera.index') }}" class="sidebar-link {{ $esActiva('categorias-madera.*') ? 'active' : '' }}"><span class="inline-flex items-center gap-1.5"><flux:icon.tag class="size-3.5" /> Categorías de madera</span></a>
                @endcan
                @can('ver-unidades-medida')
                <a href="{{ route('unidades-medida.index') }}" class="sidebar-link {{ $esActiva('unidades-medida.*') ? 'active' : '' }}"><span class="inline-flex items-center gap-1.5"><flux:icon.scale class="size-3.5" /> Unidades de medida</span></a>
                @endcan
                @can('ver-lista-precios')
                <a href="{{ route('lista-precios.index') }}" class="sidebar-link {{ $esActiva('lista-precios.*') ? 'active' : '' }}"><span class="inline-flex items-center gap-1.5"><flux:icon.currency-dollar class="size-3.5" /> Lista de precios</span></a>
                @endcan
                @can('ver-tipos-maquinaria')
                <a href="{{ route('tipos-maquinaria.index') }}" class="sidebar-link {{ $esActiva('tipos-maquinaria.*') ? 'active' : '' }}"><span class="inline-flex items-center gap-1.5"><flux:icon.cog-6-tooth class="size-3.5" /> Tipos de maquinaria</span></a>
                @endcan
                @can('ver-roles-laborales')
                <a href="{{ route('roles-laborales.index') }}" class="sidebar-link {{ $esActiva('roles-laborales.*') ? 'active' : '' }}"><span class="inline-flex items-center gap-1.5"><flux:icon.briefcase class="size-3.5" /> Roles laborales</span></a>
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
                'operaciones-forestales': false,
                'inventario-abastecimiento': false,
                'maquinaria': false,
                'personal-pagos': false,
                'reportes-auditoria': false,
                'administracion': false,
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
                    try {
                        this.open = JSON.parse(saved);
                    } catch (e) {
                        // Conservar estado por defecto si localStorage está corrupto.
                    }
                }
            },
            initSidebar() {
                this.loadSidebarState();
            }
        }
    }
</script>
