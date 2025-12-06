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
    <style>
        :root {
            --navbar-height: 56px;
            --sidebar-width: 280px;
            --footer-height: 56px;
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
            height: calc(100vh - var(--navbar-height));
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
            height: calc(100vh - var(--navbar-height) - var(--footer-height));
            padding-bottom: 0.5rem; /* que no se esconda el último item */
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
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>

    <!-- Livewire Styles -->
    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::styles(); ?>

</head>
<body class="d-flex flex-column" style="height: 100vh;">
    <?php echo $__env->make('partials.header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <!-- Sidebar Overlay (for mobile) -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>


    <div class="layout-container">
        <?php echo $__env->make('partials.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <!-- Main Content + Footer -->
        <div class="page-wrapper flex-grow-1 d-flex flex-column bg-light" id="pageWrapper">
            <main class="main-content flex-fill" id="mainContent">
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
                    
                    <?php if(isset($slot)): ?>
                        <?php echo e($slot); ?>

                    <?php else: ?>
                        <?php echo $__env->yieldContent('content'); ?>
                    <?php endif; ?>
                    
                </div>
            </main>
            <?php echo $__env->make('partials.footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
    </div>

    <?php echo $__env->yieldPushContent('scripts'); ?>

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
    </script>

    <!-- Livewire Scripts (incluye Alpine.js automáticamente en v3) -->
    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::scripts(); ?>

</body>
</html><?php /**PATH /var/www/html/resources/views/layouts/app.blade.php ENDPATH**/ ?>