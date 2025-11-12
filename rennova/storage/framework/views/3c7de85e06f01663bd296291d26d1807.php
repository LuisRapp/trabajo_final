<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <title><?php echo e(config('app.name', 'Laravel')); ?></title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Custom Styles -->
    <style>
        :root {
            --sidebar-width: 280px;
            --navbar-height: 56px;
        }

        body {
            overflow-x: hidden;
        }

        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            top: var(--navbar-height);
            left: 0;
            height: calc(100vh - var(--navbar-height));
            width: var(--sidebar-width);
            background: #fff;
            border-right: 1px solid #dee2e6;
            transition: transform 0.3s ease-in-out;
            z-index: 1040;
            overflow-y: auto;
            overflow-x: hidden;
        }

        .sidebar.collapsed {
            transform: translateX(-100%);
        }

        .sidebar-header {
            padding: 1rem;
            border-bottom: 1px solid #dee2e6;
            background: #f8f9fa;
        }

        .sidebar-section {
            padding: 0.5rem 1rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            color: #6c757d;
            background: #f8f9fa;
            margin-top: 0.5rem;
        }

        .sidebar-section:first-child {
            margin-top: 0;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            color: #495057;
            text-decoration: none;
            transition: all 0.2s;
            border-left: 3px solid transparent;
        }

        .sidebar-link:hover {
            background: #f8f9fa;
            color: #0d6efd;
            border-left-color: #0d6efd;
            padding-left: 1.25rem;
        }

        .sidebar-link.active {
            background: #e7f1ff;
            color: #0d6efd;
            border-left-color: #0d6efd;
            font-weight: 500;
        }

        .sidebar-link i {
            margin-right: 0.5rem;
            width: 20px;
            text-align: center;
        }

        /* Main Content Styles */
        .main-content {
            transition: margin-left 0.3s ease-in-out;
            margin-left: var(--sidebar-width);
            padding-top: var(--navbar-height);
            display: flex;
            flex-direction: column;
        }

        .main-content.expanded {
            margin-left: 0;
        }

        /* Navbar Styles */
        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1050;
            height: var(--navbar-height);
        }

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
            top: var(--navbar-height);
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

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0 !important;
            }
        }

        /* Scrollbar Styles */
        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 3px;
        }

        .sidebar::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
    </style>

    <!-- Scripts -->
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>

    <!-- Livewire Styles -->
    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::styles(); ?>

</head>
<body class="d-flex flex-column min-vh-100">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold fs-4" id="sidebarToggle">
                <i class="bi bi-list toggle-icon" id="toggleIcon"></i>
                Rennova
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo e(route('home')); ?>">
                            <i class="bi bi-house-door"></i> Inicio
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> Usuario
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="<?php echo e(route('dashboard')); ?>"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                            <li><a class="dropdown-item" href="<?php echo e(route('profile.edit')); ?>"><i class="bi bi-person"></i> Perfil</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="<?php echo e(route('logout')); ?>">
                                    <?php echo csrf_field(); ?>
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

    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h6 class="mb-0 text-primary"><i class="bi bi-grid-3x3-gap"></i> Módulos</h6>
        </div>

        <!-- Principal -->
        <div class="sidebar-section">
            <i class="bi bi-star-fill"></i> Principal
        </div>
        <a href="<?php echo e(route('lotes.index')); ?>" class="sidebar-link">
            <i class="bi bi-geo-alt"></i> Lotes
        </a>
        <a href="<?php echo e(route('clientes.index')); ?>" class="sidebar-link">
            <i class="bi bi-people"></i> Clientes
        </a>
        <a href="<?php echo e(route('proveedores.index')); ?>" class="sidebar-link">
            <i class="bi bi-truck"></i> Proveedores
        </a>
        <a href="<?php echo e(route('ventas.index')); ?>" class="sidebar-link">
            <i class="bi bi-receipt"></i> Ventas
        </a>
        <a href="<?php echo e(route('cargas.index')); ?>" class="sidebar-link">
            <i class="bi bi-box-seam"></i> Cargas
        </a>
        <a href="<?php echo e(route('choferes.index')); ?>" class="sidebar-link">
            <i class="bi bi-person-vcard"></i> Choferes
        </a>

        <!-- Recursos -->
        <div class="sidebar-section">
            <i class="bi bi-tools"></i> Recursos
        </div>
        <a href="<?php echo e(route('insumos.index')); ?>" class="sidebar-link">
            <i class="bi bi-box-seam"></i> Insumos
        </a>
        <a href="<?php echo e(route('maquinarias.index')); ?>" class="sidebar-link">
            <i class="bi bi-truck"></i> Maquinarias
        </a>
        <a href="<?php echo e(route('mantenimientos.index')); ?>" class="sidebar-link">
            <i class="bi bi-tools"></i> Mantenimientos
        </a>
        <a href="<?php echo e(route('kits-mantenimiento.index')); ?>" class="sidebar-link">
            <i class="bi bi-gear-fill"></i> Kits de Mantenimiento
        </a>

        <!-- Personal -->
        <div class="sidebar-section">
            <i class="bi bi-people-fill"></i> Personal
        </div>
        <a href="<?php echo e(route('empleados.index')); ?>" class="sidebar-link">
            <i class="bi bi-person-workspace"></i> Empleados
        </a>
        <a href="<?php echo e(route('adelantos.index')); ?>" class="sidebar-link">
            <i class="bi bi-cash-coin"></i> Adelantos
        </a>
        <a href="<?php echo e(route('recibos.index')); ?>" class="sidebar-link">
            <i class="bi bi-file-earmark-text"></i> Recibos
        </a>
        <a href="<?php echo e(route('liquidacion-pagos.index')); ?>" class="sidebar-link">
            <i class="bi bi-calculator"></i> Liquidación de Pagos
        </a>
        <a href="<?php echo e(route('asignaciones-lote.index')); ?>" class="sidebar-link">
            <i class="bi bi-link-45deg"></i> Asignaciones por Lote
        </a>

        <!-- Operaciones -->
        <div class="sidebar-section">
            <i class="bi bi-clipboard-check"></i> Operaciones
        </div>
        <a href="<?php echo e(route('partes-diarios.index')); ?>" class="sidebar-link">
            <i class="bi bi-clipboard-check"></i> Partes Diarios
        </a>

        <!-- Históricos -->
        <div class="sidebar-section">
            <i class="bi bi-clock-history"></i> Históricos
        </div>
        <a href="<?php echo e(route('historico-costos-maquinarias.index')); ?>" class="sidebar-link">
            <i class="bi bi-graph-up"></i> Costos Maquinarias
        </a>
        <a href="<?php echo e(route('historico-roles-laborales.index')); ?>" class="sidebar-link">
            <i class="bi bi-person-badge"></i> Roles Laborales
        </a>

        <!-- Configuración -->
        <div class="sidebar-section">
            <i class="bi bi-gear-fill"></i> Configuración
        </div>
        <a href="<?php echo e(route('categorias-madera.index')); ?>" class="sidebar-link">
            <i class="bi bi-tree"></i> Categorías Madera
        </a>
        <a href="<?php echo e(route('lista-precios.index')); ?>" class="sidebar-link">
            <i class="bi bi-tags"></i> Lista de Precios
        </a>
        <a href="<?php echo e(route('unidades-medida.index')); ?>" class="sidebar-link">
            <i class="bi bi-rulers"></i> Unidades de Medida
        </a>
        <a href="<?php echo e(route('tipos-maquinaria.index')); ?>" class="sidebar-link">
            <i class="bi bi-gear-wide-connected"></i> Tipos Maquinaria
        </a>
        <a href="<?php echo e(route('roles-laborales.index')); ?>" class="sidebar-link">
            <i class="bi bi-person-badge"></i> Roles Laborales
        </a>
        <a href="<?php echo e(route('usuarios.index')); ?>" class="sidebar-link">
            <i class="bi bi-person-circle"></i> Usuarios
        </a>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('gestionar-permisos')): ?>
        <a href="<?php echo e(route('roles-permisos.index')); ?>" class="sidebar-link">
            <i class="bi bi-shield-lock"></i> Roles y Permisos
        </a>
        <?php endif; ?>
    </aside>

    <!-- Main Content -->
    <main class="main-content bg-light flex-fill" id="mainContent">
        <div class="flex-fill">
            <?php if(session('status')): ?>
                <div class="container py-2">
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle-fill"></i> <?php echo e(session('status')); ?>

                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if(session('error')): ?>
                <div class="container py-2">
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle-fill"></i> <?php echo e(session('error')); ?>

                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php echo $__env->yieldContent('content'); ?>
        </div>

        <!-- Footer -->
        <footer class="bg-dark text-white text-center py-3 mt-auto">
            <div class="container">
                <p class="mb-0">© <?php echo e(date('Y')); ?> Rapp Luis - Todos los derechos reservados</p>
            </div>
        </footer>
    </main>

    <?php echo $__env->yieldPushContent('scripts'); ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Sidebar Toggle Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            const sidebarOverlay = document.getElementById('sidebarOverlay');
            const toggleIcon = document.getElementById('toggleIcon');
            
            // Cargar estado del sidebar desde localStorage
            const sidebarState = localStorage.getItem('sidebarCollapsed');
            if (sidebarState === 'true') {
                sidebar.classList.add('collapsed');
                mainContent.classList.add('expanded');
                toggleIcon.classList.add('rotated');
            }

            // Toggle sidebar al hacer clic en el logo
            sidebarToggle.addEventListener('click', function(e) {
                e.preventDefault();
                
                if (window.innerWidth <= 768) {
                    // En móvil, mostrar/ocultar sin cambiar el margin
                    sidebar.classList.toggle('show');
                    sidebarOverlay.classList.toggle('show');
                } else {
                    // En desktop, colapsar/expandir
                    sidebar.classList.toggle('collapsed');
                    mainContent.classList.toggle('expanded');
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
            const currentPath = window.location.pathname;
            const sidebarLinks = document.querySelectorAll('.sidebar-link');
            sidebarLinks.forEach(link => {
                if (link.getAttribute('href') === currentPath) {
                    link.classList.add('active');
                }
            });

            // Responsive: resetear estado en cambio de tamaño
            let resizeTimer;
            window.addEventListener('resize', function() {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(function() {
                    if (window.innerWidth <= 768) {
                        sidebar.classList.remove('show');
                        sidebarOverlay.classList.remove('show');
                    }
                }, 250);
            });
        });
    </script>

    <!-- Livewire Scripts -->
    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::scripts(); ?>

</body>
</html><?php /**PATH D:\trabajo_final\rennova\resources\views/layouts/app.blade.php ENDPATH**/ ?>