<aside
    class="sidebar"
    :class="{ 'collapsed': collapsed, 'show': mobileOpen }"
    x-data="sidebarState()"
    x-init="initSidebar()"
    id="sidebar"
>
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

        $esActiva = fn($routeName) => request()->routeIs($routeName) || request()->routeIs($routeName . '.*');
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
                <span class="inline-flex items-center gap-1.5"><flux:icon.star class="size-3.5" /> Principal</span>
                <span class="text-xs transition-transform duration-300 inline-block" :class="{ 'rotate-180': open.principal }">▼</span>
            </button>
            <div
                id="menuPrincipal"
                x-show="open.principal"
                x-transition
                class="sidebar-submenu"
            >
                @can('ver-lotes')
                <a href="{{ route('lotes.index') }}" class="sidebar-link {{ $esActiva('lotes.index') ? 'active' : '' }}"><span class="inline-flex items-center gap-1.5"><flux:icon.map-pin class="size-3.5" /> Lotes</span></a>
                @endcan
                @can('ver-clientes')
                <a href="{{ route('clientes.index') }}" class="sidebar-link {{ $esActiva('clientes.index') ? 'active' : '' }}"><span class="inline-flex items-center gap-1.5"><flux:icon.users class="size-3.5" /> Clientes</span></a>
                @endcan
                @can('ver-proveedores')
                <a href="{{ route('proveedores.index') }}" class="sidebar-link {{ $esActiva('proveedores.index') ? 'active' : '' }}"><span class="inline-flex items-center gap-1.5"><flux:icon.truck class="size-3.5" /> Proveedores</span></a>
                @endcan
                @can('ver-ventas')
                <a href="{{ route('ventas.index') }}" class="sidebar-link {{ $esActiva('ventas.index') ? 'active' : '' }}"><span class="inline-flex items-center gap-1.5"><flux:icon.document class="size-3.5" /> Ventas</span></a>
                @endcan
                @can('ver-cargas')
                <a href="{{ route('cargas.index') }}" class="sidebar-link {{ $esActiva('cargas.index') ? 'active' : '' }}"><span class="inline-flex items-center gap-1.5"><flux:icon.cube class="size-3.5" /> Cargas</span></a>
                @endcan
                @can('ver-choferes')
                <a href="{{ route('choferes.index') }}" class="sidebar-link {{ $esActiva('choferes.index') ? 'active' : '' }}"><span class="inline-flex items-center gap-1.5"><flux:icon.identification class="size-3.5" /> Choferes</span></a>
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
                <span class="inline-flex items-center gap-1.5"><flux:icon.wrench class="size-3.5" /> Recursos</span>
                <span class="text-xs transition-transform duration-300 inline-block" :class="{ 'rotate-180': open.recursos }">▼</span>
            </button>
            <div
                id="menuRecursos"
                x-show="open.recursos"
                x-transition
                class="sidebar-submenu"
            >
                @can('ver-insumos')
                <a href="{{ route('insumos.index') }}" class="sidebar-link {{ $esActiva('insumos.index') ? 'active' : '' }}"><span class="inline-flex items-center gap-1.5"><flux:icon.cube class="size-3.5" /> Insumos</span></a>
                @endcan
                @can('ver-maquinarias')
                <a href="{{ route('maquinarias.index') }}" class="sidebar-link {{ $esActiva('maquinarias.index') ? 'active' : '' }}"><span class="inline-flex items-center gap-1.5"><flux:icon.truck class="size-3.5" /> Maquinarias</span></a>
                @endcan
                @can('ver-mantenimientos')
                <a href="{{ route('mantenimientos.index') }}" class="sidebar-link {{ $esActiva('mantenimientos.index') ? 'active' : '' }}"><span class="inline-flex items-center gap-1.5"><flux:icon.wrench class="size-3.5" /> Mantenimientos</span></a>
                @endcan
                @can('ver-kits-mantenimiento')
                <a href="{{ route('kits-mantenimiento.index') }}" class="sidebar-link {{ $esActiva('kits-mantenimiento.index') ? 'active' : '' }}"><span class="inline-flex items-center gap-1.5"><flux:icon.cog class="size-3.5" /> Kits de Mantenimiento</span></a>
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
                <span class="inline-flex items-center gap-1.5"><flux:icon.users class="size-3.5" /> Personal</span>
                <span class="text-xs transition-transform duration-300 inline-block" :class="{ 'rotate-180': open.personal }">▼</span>
            </button>
            <div
                id="menuPersonal"
                x-show="open.personal"
                x-transition
                class="sidebar-submenu"
            >
                @can('ver-empleados')
                <a href="{{ route('empleados.index') }}" class="sidebar-link {{ $esActiva('empleados.index') ? 'active' : '' }}"><span class="inline-flex items-center gap-1.5"><flux:icon.user class="size-3.5" /> Empleados</span></a>
                @endcan
                @can('ver-adelantos')
                <a href="{{ route('adelantos.index') }}" class="sidebar-link {{ $esActiva('adelantos.index') ? 'active' : '' }}"><span class="inline-flex items-center gap-1.5"><flux:icon.banknotes class="size-3.5" /> Adelantos</span></a>
                @endcan
                @can('ver-recibos')
                <a href="{{ route('recibos.index') }}" class="sidebar-link {{ $esActiva('recibos.index') ? 'active' : '' }}"><span class="inline-flex items-center gap-1.5"><flux:icon.document-text class="size-3.5" /> Recibos</span></a>
                @endcan
                @can('ver-liquidacion-pagos')
                <a href="{{ route('liquidacion-pagos.index') }}" class="sidebar-link {{ $esActiva('liquidacion-pagos.index') ? 'active' : '' }}"><span class="inline-flex items-center gap-1.5"><flux:icon.calculator class="size-3.5" /> Liquidación de Pagos</span></a>
                @endcan
                @can('ver-asignaciones-lote')
                <a href="{{ route('asignaciones-lote.index') }}" class="sidebar-link {{ $esActiva('asignaciones-lote.index') ? 'active' : '' }}"><span class="inline-flex items-center gap-1.5"><flux:icon.link class="size-3.5" /> Asignaciones por Lote</span></a>
                @endcan
                @can('ver-propuestas-asignacion')
                <a href="{{ route('allocation-proposals.index') }}" class="sidebar-link {{ $esActiva('allocation-proposals.index') ? 'active' : '' }}"><span class="inline-flex items-center gap-1.5"><flux:icon.sparkles class="size-3.5" /> Propuestas Automáticas</span></a>
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
                <span class="inline-flex items-center gap-1.5"><flux:icon.clipboard-document-list class="size-3.5" /> Operaciones</span>
                <span class="text-xs transition-transform duration-300 inline-block" :class="{ 'rotate-180': open.operaciones }">▼</span>
            </button>
            <div
                id="menuOperaciones"
                x-show="open.operaciones"
                x-transition
                class="sidebar-submenu"
            >
                @can('ver-partes-diarios')
                <a href="{{ route('partes-diarios.index') }}" class="sidebar-link {{ $esActiva('partes-diarios.index') ? 'active' : '' }}"><span class="inline-flex items-center gap-1.5"><flux:icon.clipboard-document-list class="size-3.5" /> Partes Diarios</span></a>
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
                <span class="inline-flex items-center gap-1.5"><flux:icon.clock class="size-3.5" /> Históricos</span>
                <span class="text-xs transition-transform duration-300 inline-block" :class="{ 'rotate-180': open.historicos }">▼</span>
            </button>
            <div
                id="menuHistoricos"
                x-show="open.historicos"
                x-transition
                class="sidebar-submenu"
            >
                @can('ver-historico-costos-maquinarias')
                <a href="{{ route('historico-costos-maquinarias.index') }}" class="sidebar-link {{ $esActiva('historico-costos-maquinarias.index') ? 'active' : '' }}"><span class="inline-flex items-center gap-1.5"><flux:icon.chart-bar class="size-3.5" /> Costos Maquinarias</span></a>
                @endcan
                @can('ver-roles-laborales')
                <a href="{{ route('historico-roles-laborales.index') }}" class="sidebar-link {{ $esActiva('historico-roles-laborales.index') ? 'active' : '' }}"><span class="inline-flex items-center gap-1.5"><flux:icon.identification class="size-3.5" /> Roles Laborales</span></a>
                @endcan
                @can('ver-auditoria')
                <a href="{{ route('auditorias.index') }}" class="sidebar-link {{ $esActiva('auditorias.index') ? 'active' : '' }}"><span class="inline-flex items-center gap-1.5"><flux:icon.document-text class="size-3.5" /> Auditorías</span></a>
                @endcan
                @can('ver-reportes')
                <a href="{{ route('reportes.estadisticas-forestales') }}" class="sidebar-link {{ $esActiva('reportes.estadisticas-forestales') ? 'active' : '' }}"><span class="inline-flex items-center gap-1.5"><flux:icon.chart-bar class="size-3.5" /> Estadísticas Forestales</span></a>
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
                <span class="inline-flex items-center gap-1.5"><flux:icon.cog class="size-3.5" /> Configuración</span>
                <span class="text-xs transition-transform duration-300 inline-block" :class="{ 'rotate-180': open.configuracion }">▼</span>
            </button>
            <div
                id="menuConfiguracion"
                x-show="open.configuracion"
                x-transition
                class="sidebar-submenu"
            >
                @can('ver-categorias-madera')
                <a href="{{ route('categorias-madera.index') }}" class="sidebar-link {{ $esActiva('categorias-madera.index') ? 'active' : '' }}"><span class="inline-flex items-center gap-1.5"><flux:icon.tag class="size-3.5" /> Categorías Madera</span></a>
                @endcan
                @can('ver-lista-precios')
                <a href="{{ route('lista-precios.index') }}" class="sidebar-link {{ $esActiva('lista-precios.index') ? 'active' : '' }}"><span class="inline-flex items-center gap-1.5"><flux:icon.tag class="size-3.5" /> Lista de Precios</span></a>
                @endcan
                @can('ver-unidades-medida')
                <a href="{{ route('unidades-medida.index') }}" class="sidebar-link {{ $esActiva('unidades-medida.index') ? 'active' : '' }}"><span class="inline-flex items-center gap-1.5"><flux:icon.scale class="size-3.5" /> Unidades de Medida</span></a>
                @endcan
                @can('ver-tipos-maquinaria')
                <a href="{{ route('tipos-maquinaria.index') }}" class="sidebar-link {{ $esActiva('tipos-maquinaria.index') ? 'active' : '' }}"><span class="inline-flex items-center gap-1.5"><flux:icon.cog class="size-3.5" /> Tipos Maquinaria</span></a>
                @endcan
                @can('ver-roles-laborales')
                <a href="{{ route('roles-laborales.index') }}" class="sidebar-link {{ $esActiva('roles-laborales.index') ? 'active' : '' }}"><span class="inline-flex items-center gap-1.5"><flux:icon.identification class="size-3.5" /> Roles Laborales</span></a>
                @endcan
                @can('gestionar-usuarios')
                <a href="{{ route('usuarios.index') }}" class="sidebar-link {{ $esActiva('usuarios.index') ? 'active' : '' }}"><span class="inline-flex items-center gap-1.5"><flux:icon.user class="size-3.5" /> Usuarios</span></a>
                @endcan
                @can('gestionar-permisos')
                <a href="{{ route('roles-permisos.index') }}" class="sidebar-link {{ $esActiva('roles-permisos.index') ? 'active' : '' }}"><span class="inline-flex items-center gap-1.5"><flux:icon.lock-closed class="size-3.5" /> Roles y Permisos</span></a>
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
