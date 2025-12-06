<aside class="sidebar" id="sidebar">
    <div class="py-2">
        <button class="sidebar-collapse-btn" type="button" data-bs-toggle="collapse" data-bs-target="#menuPrincipal">
            <span><i class="bi bi-star-fill me-2"></i> Principal</span> <i class="bi bi-chevron-down"></i>
        </button>
        <div class="collapse show" id="menuPrincipal">
            <a href="{{ route('lotes.index') }}" class="sidebar-link ms-3"><i class="bi bi-geo-alt"></i> Lotes</a>
            <a href="{{ route('clientes.index') }}" class="sidebar-link ms-3"><i class="bi bi-people"></i> Clientes</a>
            <a href="{{ route('proveedores.index') }}" class="sidebar-link ms-3"><i class="bi bi-truck"></i> Proveedores</a>
            <a href="{{ route('ventas.index') }}" class="sidebar-link ms-3"><i class="bi bi-receipt"></i> Ventas</a>
            <a href="{{ route('cargas.index') }}" class="sidebar-link ms-3"><i class="bi bi-box-seam"></i> Cargas</a>
            <a href="{{ route('choferes.index') }}" class="sidebar-link ms-3"><i class="bi bi-person-vcard"></i> Choferes</a>
        </div>

        <button class="sidebar-collapse-btn" type="button" data-bs-toggle="collapse" data-bs-target="#menuRecursos">
            <span><i class="bi bi-tools me-2"></i> Recursos</span> <i class="bi bi-chevron-down"></i>
        </button>
        <div class="collapse" id="menuRecursos">
            <a href="{{ route('insumos.index') }}" class="sidebar-link ms-3"><i class="bi bi-box-seam"></i> Insumos</a>
            <a href="{{ route('maquinarias.index') }}" class="sidebar-link ms-3"><i class="bi bi-truck"></i> Maquinarias</a>
            <a href="{{ route('mantenimientos.index') }}" class="sidebar-link ms-3"><i class="bi bi-tools"></i> Mantenimientos</a>
            <a href="{{ route('kits-mantenimiento.index') }}" class="sidebar-link ms-3"><i class="bi bi-gear-fill"></i> Kits de Mantenimiento</a>
        </div>

        <button class="sidebar-collapse-btn" type="button" data-bs-toggle="collapse" data-bs-target="#menuPersonal">
            <span><i class="bi bi-people-fill me-2"></i> Personal</span> <i class="bi bi-chevron-down"></i>
        </button>
        <div class="collapse" id="menuPersonal">
            <a href="{{ route('empleados.index') }}" class="sidebar-link ms-3"><i class="bi bi-person-workspace"></i> Empleados</a>
            <a href="{{ route('adelantos.index') }}" class="sidebar-link ms-3"><i class="bi bi-cash-coin"></i> Adelantos</a>
            <a href="{{ route('recibos.index') }}" class="sidebar-link ms-3"><i class="bi bi-file-earmark-text"></i> Recibos</a>
            <a href="{{ route('liquidacion-pagos.index') }}" class="sidebar-link ms-3"><i class="bi bi-calculator"></i> Liquidación de Pagos</a>
            <a href="{{ route('asignaciones-lote.index') }}" class="sidebar-link ms-3"><i class="bi bi-link-45deg"></i> Asignaciones por Lote</a>
        </div>

        <button class="sidebar-collapse-btn" type="button" data-bs-toggle="collapse" data-bs-target="#menuOperaciones">
            <span><i class="bi bi-clipboard-check me-2"></i> Operaciones</span> <i class="bi bi-chevron-down"></i>
        </button>
        <div class="collapse" id="menuOperaciones">
            <a href="{{ route('partes-diarios.index') }}" class="sidebar-link ms-3"><i class="bi bi-clipboard-check"></i> Partes Diarios</a>
        </div>

        <button class="sidebar-collapse-btn" type="button" data-bs-toggle="collapse" data-bs-target="#menuHistoricos">
            <span><i class="bi bi-clock-history me-2"></i> Históricos</span> <i class="bi bi-chevron-down"></i>
        </button>
        <div class="collapse" id="menuHistoricos">
            <a href="{{ route('historico-costos-maquinarias.index') }}" class="sidebar-link ms-3"><i class="bi bi-graph-up"></i> Costos Maquinarias</a>
            <a href="{{ route('historico-roles-laborales.index') }}" class="sidebar-link ms-3"><i class="bi bi-person-badge"></i> Roles Laborales</a>
            <a href="{{ route('auditorias.index') }}" class="sidebar-link ms-3"><i class="bi bi-file-earmark-text"></i> Auditorías</a>
        </div>

        <button class="sidebar-collapse-btn" type="button" data-bs-toggle="collapse" data-bs-target="#menuConfiguracion">
            <span><i class="bi bi-gear-fill me-2"></i> Configuración</span> <i class="bi bi-chevron-down"></i>
        </button>
        <div class="collapse" id="menuConfiguracion">
            <a href="{{ route('categorias-madera.index') }}" class="sidebar-link ms-3"><i class="bi bi-tree"></i> Categorías Madera</a>
            <a href="{{ route('lista-precios.index') }}" class="sidebar-link ms-3"><i class="bi bi-tags"></i> Lista de Precios</a>
            <a href="{{ route('unidades-medida.index') }}" class="sidebar-link ms-3"><i class="bi bi-rulers"></i> Unidades de Medida</a>
            <a href="{{ route('tipos-maquinaria.index') }}" class="sidebar-link ms-3"><i class="bi bi-gear-wide-connected"></i> Tipos Maquinaria</a>
            <a href="{{ route('roles-laborales.index') }}" class="sidebar-link ms-3"><i class="bi bi-person-badge"></i> Roles Laborales</a>
            <a href="{{ route('usuarios.index') }}" class="sidebar-link ms-3"><i class="bi bi-person-circle"></i> Usuarios</a>
            @can('gestionar-permisos')
            <a href="{{ route('roles-permisos.index') }}" class="sidebar-link ms-3"><i class="bi bi-shield-lock"></i> Roles y Permisos</a>
            @endcan
        </div>
    </div>
</aside>
