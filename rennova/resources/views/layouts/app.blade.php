<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root {
            --navbar-height: 56px;
            --sidebar-width: 280px;
            --primary-color: #2A6041;
            --primary-light: #C8D6AF;
            --bg-light: #F4F7F6;
            --sidebar-bg: #FFFFFF;
            --sidebar-header-bg: #F4F7F6;
        }

        body {
            background-color: var(--bg-light);
            margin: 0;
            padding: 0;
        }

        /* Navbar */
        .navbar {
            flex-shrink: 0;
        }

        /* Sidebar Styles */
        .layout-container {
            display: flex;
            flex: 1;
            overflow: hidden;
            min-height: 0;
        }
        .sidebar {
            width: var(--sidebar-width);
            min-width: var(--sidebar-width);
            background: var(--sidebar-bg);
            color: #343A40;
            overflow-y: auto;
            box-shadow: 2px 0 8px rgba(0, 0, 0, 0.08);
            transition: margin-left 0.3s ease-in-out;
            flex-shrink: 0;
        }
        .sidebar.collapsed {
            margin-left: calc(-1 * var(--sidebar-width));
        }
        .sidebar-header {
            padding: 1rem;
            font-weight: bold;
            background: var(--sidebar-header-bg);
        }
        .sidebar-header h6 {
            color: var(--primary-color);
        }
        .sidebar-section {
            padding: 0.75rem 1rem 0.25rem 1rem;
            font-size: 0.95rem;
            color: var(--primary-color);
            font-weight: 600;
        }
        .sidebar-link {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            color: #343A40;
            text-decoration: none;
            transition: all 0.2s;
            border-left: 3px solid transparent;
        }
        .sidebar-link:hover {
            background: var(--bg-light);
            color: var(--primary-color);
            border-left-color: var(--primary-color);
            padding-left: 1.25rem;
        }
        .sidebar-link.active {
            background: var(--bg-light);
            color: var(--primary-color);
            border-left-color: var(--primary-color);
            font-weight: 500;
        }
        .sidebar-link i {
            margin-right: 0.5rem;
            width: 20px;
            text-align: center;
        }

        /* Sidebar collapse sections */
        .sidebar-collapse-btn {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.75rem 1rem;
            color: var(--primary-color);
            background: transparent;
            border: none;
            text-decoration: none;
            transition: all 0.2s;
            cursor: pointer;
            width: 100%;
            text-align: left;
            font-weight: 600;
            font-size: 0.95rem;
        }
        .sidebar-collapse-btn:hover {
            background: var(--bg-light);
        }
        .sidebar-collapse-btn i.toggle-arrow {
            transition: transform 0.3s;
            font-size: 0.8rem;
        }
        .sidebar-collapse-btn.collapsed i.toggle-arrow {
            transform: rotate(-90deg);
        }
        .sidebar-submenu {
            padding-left: 1rem;
        }
        .sidebar-submenu .sidebar-link {
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
        }

        /* Page wrapper */
        .page-wrapper {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 0;
            overflow: hidden;
        }

        /* Main content */
        .main-content {
            padding: 2rem;
            overflow-y: auto;
            flex: 1;
        }

        /* Footer */
        footer {
            flex-shrink: 0;
        }
        
        /* Navbar Styles */
        .navbar-brand {
            cursor: pointer;
            user-select: none;
        }
        .navbar-brand:hover {
            opacity: 0.9;
        }
        .toggle-icon {
            transition: transform 0.3s;
            display: inline-block;
            margin-right: 0.5rem;
        }
        .toggle-icon.rotated {
            transform: rotate(90deg);
        }

        /* Overlay for mobile */
        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1039;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s, visibility 0.3s;
        }
        .sidebar-overlay.show {
            opacity: 1;
            visibility: visible;
        }

        /* Scrollbar Styles */
        .sidebar::-webkit-scrollbar {
            width: 6px;
        }
        .sidebar::-webkit-scrollbar-track {
            background: var(--sidebar-header-bg);
        }
        .sidebar::-webkit-scrollbar-thumb {
            background: #C8D6AF;
            border-radius: 3px;
        }
        .sidebar::-webkit-scrollbar-thumb:hover {
            background: var(--primary-color);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                top: var(--navbar-height);
                left: 0;
                height: calc(100vh - var(--navbar-height));
                z-index: 1040;
                transform: translateX(-100%);
            }
            .sidebar.show {
                transform: translateX(0);
            }
            .sidebar.collapsed {
                margin-left: 0;
            }
            .main-content {
                padding: 1rem;
            }
        }
    </style>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Livewire Styles -->
    @livewireStyles
</head>
<body class="d-flex flex-column" style="height: 100vh;">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark shadow" style="background-color: #2A6041;">
        <div class="container-fluid">
            <div class="d-flex align-items-center">
                <span id="sidebarToggle" style="cursor: pointer; margin-right: 0.5rem;">
                    <i class="bi bi-list toggle-icon" id="toggleIcon" style="font-size: 1.5rem; color: white;"></i>
                </span>
                <a class="navbar-brand fw-bold fs-4 mb-0" href="{{ route('home') }}">
                    Rennova
                </a>
            </div>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <!-- Campana de notificaciones SIEMPRE visible para usuarios autenticados -->
                    @if(auth()->check())
                        @livewire('notificaciones-campana')
                    @endif
                    
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('home') }}">
                            <i class="bi bi-house-door"></i> Inicio
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> Usuario
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('dashboard') }}"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                            <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="bi bi-person"></i> Perfil</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item"><i class="bi bi-box-arrow-right"></i> Cerrar sesión</button>
                                </form>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Sidebar Overlay (for mobile) -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>


    <div class="layout-container">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <h6 class="mb-0"><i class="bi bi-grid-3x3-gap"></i> Módulos</h6>
            </div>
            
            <!-- Principal -->
            <button class="sidebar-collapse-btn" type="button" data-bs-toggle="collapse" data-bs-target="#menuPrincipal">
                <span><i class="bi bi-star-fill"></i> Principal</span>
                <i class="bi bi-chevron-down toggle-arrow"></i>
            </button>
            <div class="collapse show sidebar-submenu" id="menuPrincipal">
                <a href="{{ route('lotes.index') }}" class="sidebar-link">
                    <i class="bi bi-geo-alt"></i> Lotes
                </a>
                <a href="{{ route('clientes.index') }}" class="sidebar-link">
                    <i class="bi bi-people"></i> Clientes
                </a>
                <a href="{{ route('proveedores.index') }}" class="sidebar-link">
                    <i class="bi bi-truck"></i> Proveedores
                </a>
                <a href="{{ route('ventas.index') }}" class="sidebar-link">
                    <i class="bi bi-receipt"></i> Ventas
                </a>
                <a href="{{ route('cargas.index') }}" class="sidebar-link">
                    <i class="bi bi-box-seam"></i> Cargas
                </a>
                <a href="{{ route('choferes.index') }}" class="sidebar-link">
                    <i class="bi bi-person-vcard"></i> Choferes
                </a>
            </div>

            <!-- Recursos -->
            <button class="sidebar-collapse-btn collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#menuRecursos">
                <span><i class="bi bi-tools"></i> Recursos</span>
                <i class="bi bi-chevron-down toggle-arrow"></i>
            </button>
            <div class="collapse sidebar-submenu" id="menuRecursos">
                <a href="{{ route('insumos.index') }}" class="sidebar-link">
                    <i class="bi bi-box-seam"></i> Insumos
                </a>
                <a href="{{ route('maquinarias.index') }}" class="sidebar-link">
                    <i class="bi bi-truck"></i> Maquinarias
                </a>
                <a href="{{ route('mantenimientos.index') }}" class="sidebar-link">
                    <i class="bi bi-tools"></i> Mantenimientos
                </a>
                <a href="{{ route('kits-mantenimiento.index') }}" class="sidebar-link">
                    <i class="bi bi-gear-fill"></i> Kits de Mantenimiento
                </a>
            </div>

            <!-- Personal -->
            <button class="sidebar-collapse-btn collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#menuPersonal">
                <span><i class="bi bi-people-fill"></i> Personal</span>
                <i class="bi bi-chevron-down toggle-arrow"></i>
            </button>
            <div class="collapse sidebar-submenu" id="menuPersonal">
                <a href="{{ route('empleados.index') }}" class="sidebar-link">
                    <i class="bi bi-person-workspace"></i> Empleados
                </a>
                <a href="{{ route('adelantos.index') }}" class="sidebar-link">
                    <i class="bi bi-cash-coin"></i> Adelantos
                </a>
                <a href="{{ route('recibos.index') }}" class="sidebar-link">
                    <i class="bi bi-file-earmark-text"></i> Recibos
                </a>
                <a href="{{ route('liquidacion-pagos.index') }}" class="sidebar-link">
                    <i class="bi bi-calculator"></i> Liquidación de Pagos
                </a>
                <a href="{{ route('asignaciones-lote.index') }}" class="sidebar-link">
                    <i class="bi bi-link-45deg"></i> Asignaciones por Lote
                </a>
            </div>

            <!-- Operaciones -->
            <button class="sidebar-collapse-btn collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#menuOperaciones">
                <span><i class="bi bi-clipboard-check"></i> Operaciones</span>
                <i class="bi bi-chevron-down toggle-arrow"></i>
            </button>
            <div class="collapse sidebar-submenu" id="menuOperaciones">
                <a href="{{ route('partes-diarios.index') }}" class="sidebar-link">
                    <i class="bi bi-clipboard-check"></i> Partes Diarios
                </a>
            </div>

            <!-- Históricos -->
            <button class="sidebar-collapse-btn collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#menuHistoricos">
                <span><i class="bi bi-clock-history"></i> Históricos</span>
                <i class="bi bi-chevron-down toggle-arrow"></i>
            </button>
            <div class="collapse sidebar-submenu" id="menuHistoricos">
                <a href="{{ route('historico-costos-maquinarias.index') }}" class="sidebar-link">
                    <i class="bi bi-graph-up"></i> Costos Maquinarias
                </a>
                <a href="{{ route('historico-roles-laborales.index') }}" class="sidebar-link">
                    <i class="bi bi-person-badge"></i> Roles Laborales
                </a>
                <a href="{{ route('auditorias.index') }}" class="sidebar-link">
                    <i class="bi bi-file-earmark-text"></i> Auditorías
                </a>
            </div>

            <!-- Configuración -->
            <button class="sidebar-collapse-btn collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#menuConfiguracion">
                <span><i class="bi bi-gear-fill"></i> Configuración</span>
                <i class="bi bi-chevron-down toggle-arrow"></i>
            </button>
            <div class="collapse sidebar-submenu" id="menuConfiguracion">
                <a href="{{ route('categorias-madera.index') }}" class="sidebar-link">
                    <i class="bi bi-tree"></i> Categorías Madera
                </a>
                <a href="{{ route('lista-precios.index') }}" class="sidebar-link">
                    <i class="bi bi-tags"></i> Lista de Precios
                </a>
                <a href="{{ route('unidades-medida.index') }}" class="sidebar-link">
                    <i class="bi bi-rulers"></i> Unidades de Medida
                </a>
                <a href="{{ route('tipos-maquinaria.index') }}" class="sidebar-link">
                    <i class="bi bi-gear-wide-connected"></i> Tipos Maquinaria
                </a>
                <a href="{{ route('roles-laborales.index') }}" class="sidebar-link">
                    <i class="bi bi-person-badge"></i> Roles Laborales
                </a>
                <a href="{{ route('usuarios.index') }}" class="sidebar-link">
                    <i class="bi bi-person-circle"></i> Usuarios
                </a>
                @can('gestionar-permisos')
                <a href="{{ route('roles-permisos.index') }}" class="sidebar-link">
                    <i class="bi bi-shield-lock"></i> Roles y Permisos
                </a>
                @endcan
            </div>
        </aside>

        <!-- Main Content + Footer -->
        <div class="page-wrapper flex-grow-1 d-flex flex-column bg-light" id="pageWrapper">
            <main class="main-content flex-fill" id="mainContent">
                <div class="flex-fill">
                    @if(session('status'))
                        <div class="container py-2">
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="bi bi-check-circle-fill"></i> {{ session('status') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="container py-2">
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        </div>
                    @endif
                    
                    @yield('content')
                    
                </div>
            </main>
            <!-- Footer -->
            <footer class="text-white text-center py-3 mt-auto" style="background-color: #2A6041;">
                <div class="container">
                    <p class="mb-0">© {{ date('Y') }} Rapp Luis - Todos los derechos reservados</p>
                </div>
            </footer>
        </div>
    </div>

    @stack('scripts')

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Sidebar Toggle Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.getElementById('sidebar');
            // 4. Se selecciona el nuevo pageWrapper
            const pageWrapper = document.getElementById('pageWrapper');
            const sidebarOverlay = document.getElementById('sidebarOverlay');
            const toggleIcon = document.getElementById('toggleIcon');
            
            // Cargar estado del sidebar desde localStorage
            const sidebarState = localStorage.getItem('sidebarCollapsed');
            if (sidebarState === 'true') {
                sidebar.classList.add('collapsed');
                toggleIcon.classList.add('rotated');
            }

            // Toggle sidebar al hacer clic en el logo
            sidebarToggle.addEventListener('click', function(e) {
                e.preventDefault();
                
                if (window.innerWidth <= 768) {
                    // En móvil, mostrar/ocultar
                    sidebar.classList.toggle('show');
                    sidebarOverlay.classList.toggle('show');
                } else {
                    // En desktop, colapsar/expandir
                    sidebar.classList.toggle('collapsed');
                    toggleIcon.classList.toggle('rotated');
                    
                    // Guardar estado en localStorage
                    localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
                }
            });

            // Cerrar sidebar al hacer clic en el overlay (móvil)
            sidebarOverlay.addEventListener('click', function() {
                sidebar.classList.remove('show');
                sidebarOverlay.classList.remove('show');
            });

            // Marcar link activo
            const currentUrl = window.location.href; // Usar href para que coincida con las rutas completas
            const sidebarLinks = document.querySelectorAll('.sidebar-link');
            sidebarLinks.forEach(link => {
                // Comprobar si la URL actual comienza con el href del enlace
                // Esto marca como activos '.../maquinarias/1/edit' si el enlace es '.../maquinarias'
                // Pero para ser más precisos, usaremos una coincidencia más exacta si es posible.
                // Una coincidencia simple es mejor para evitar sobrecargar.
                if (link.href === currentUrl) {
                    link.classList.add('active');
                }
            });

            // Responsive: resetear estado en cambio de tamaño
            let resizeTimer;
            window.addEventListener('resize', function() {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(function() {
                    if (window.innerWidth > 768) {
                        // Si pasamos a desktop, ocultar el overlay
                        sidebar.classList.remove('show');
                        sidebarOverlay.classList.remove('show');
                    }
                }, 250);
            });
        });
    </script> <!-- <-- ESTA ERA LA ETIQUETA ROTA -->

    <!-- Alpine.js para interactividad en componentes -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Livewire Scripts -->
    @livewireScripts
</body>
</html>