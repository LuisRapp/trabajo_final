<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <title><?php echo e(config('app.name', 'Laravel')); ?></title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet">

    <!-- Scripts & Styles (Tailwind CSS via Vite) -->
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <style>
        :root {
            --navbar-height: 38px;
            --sidebar-width: 220px;
            --footer-height: 40px;
            --primary-color: #0f172a;
            --primary-light: #e2e8f0;
            --bg-light: #f8fafc;
            --sidebar-bg: #ffffff;
            --sidebar-header-bg: #f1f5f9;
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
            color: #0f172a;
            overflow-y: auto;
            box-shadow: 2px 0 8px rgba(0, 0, 0, 0.08);
            transition: margin-left 0.3s ease-in-out;
            flex-shrink: 0;
            height: 100%;
            padding-bottom: 1.5rem;
        }
        .sidebar.collapsed {
            margin-left: calc(-1 * var(--sidebar-width));
        }
        .sidebar-header {
            padding: 0.75rem;
            font-weight: bold;
            background: var(--sidebar-header-bg);
        }
        .sidebar-header h6 {
            color: var(--primary-color);
            font-size: 0.85rem;
        }
        .sidebar-section {
            padding: 0.5rem 0.75rem 0.25rem 0.75rem;
            font-size: 0.8rem;
            color: var(--primary-color);
            font-weight: 600;
        }
        .sidebar-link {
            display: flex;
            align-items: center;
            padding: 0.5rem 0.75rem;
            color: #334155;
            text-decoration: none;
            transition: all 0.2s;
            border-left: 2px solid transparent;
            font-size: 0.85rem;
        }
        .sidebar-link:hover {
            background: var(--bg-light);
            color: var(--primary-color);
            border-left-color: var(--primary-color);
            padding-left: 0.9rem;
        }
        .sidebar-link.active {
            background: var(--bg-light);
            color: var(--primary-color);
            border-left-color: var(--primary-color);
            font-weight: 500;
        }
        .sidebar-link i {
            margin-right: 0.4rem;
            width: 16px;
            text-align: center;
            font-size: 0.9rem;
        }

        /* Sidebar collapse sections */
        .sidebar-collapse-btn {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.5rem 0.75rem;
            color: var(--primary-color);
            background: transparent;
            border: none;
            text-decoration: none;
            transition: all 0.2s;
            cursor: pointer;
            width: 100%;
            text-align: left;
            font-weight: 600;
            font-size: 0.85rem;
        }
        .sidebar-collapse-btn:hover {
            background: var(--bg-light);
        }
        .sidebar-collapse-btn i.toggle-arrow {
            transition: transform 0.3s;
            font-size: 0.7rem;
        }
        .sidebar-collapse-btn.collapsed i.toggle-arrow {
            transform: rotate(-90deg);
        }
        .sidebar-submenu {
            padding-left: 0.75rem;
            background: rgba(0, 0, 0, 0.02);
        }
        .sidebar-submenu .sidebar-link {
            padding: 0.4rem 0.75rem;
            font-size: 0.8rem;
            border-left: 2px solid transparent;
        }
        .sidebar-submenu .sidebar-link:hover {
            padding-left: 0.9rem;
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
            padding: 1rem;
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
            background: #cbd5e1;
            border-radius: 3px;
        }
        .sidebar::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
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
                padding: 0.75rem;
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
        <div class="page-wrapper flex-grow-1 d-flex flex-column bg-slate-50" id="pageWrapper">
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

    <!-- Sidebar Toggle Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            initializeSidebar();
        });

        function initializeSidebar() {
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.getElementById('sidebar');
            const sidebarOverlay = document.getElementById('sidebarOverlay');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (!sidebar || !sidebarToggle) return;
            
            // Cargar estado del sidebar (expandido/colapsado) desde localStorage
            const sidebarState = localStorage.getItem('sidebarCollapsed');
            if (sidebarState === 'true') {
                sidebar.classList.add('collapsed');
                if (toggleIcon) toggleIcon.classList.add('rotated');
            }

            // Toggle sidebar al hacer clic en el logo
            sidebarToggle.addEventListener('click', function(e) {
                e.preventDefault();
                
                if (window.innerWidth <= 768) {
                    sidebar.classList.toggle('show');
                    if (sidebarOverlay) sidebarOverlay.classList.toggle('show');
                } else {
                    sidebar.classList.toggle('collapsed');
                    if (toggleIcon) toggleIcon.classList.toggle('rotated');
                    localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
                }
            });

            // Cerrar sidebar al hacer clic en el overlay (móvil)
            if (sidebarOverlay) {
                sidebarOverlay.addEventListener('click', function() {
                    sidebar.classList.remove('show');
                    sidebarOverlay.classList.remove('show');
                });
            }

            // Marcar link activo
            const currentPath = window.location.pathname.replace(/\/+$/, '') || '/';
            const sidebarLinks = document.querySelectorAll('.sidebar-link');
            
            sidebarLinks.forEach(link => {
                try {
                    const linkPath = new URL(link.href).pathname.replace(/\/+$/, '') || '/';
                    const isActive = (linkPath === '/') 
                        ? currentPath === '/'
                        : (currentPath === linkPath || currentPath.startsWith(linkPath + '/'));
                    
                    if (isActive) link.classList.add('active');
                } catch (_) {}
            });

            // Responsive: resetear estado en cambio de tamaño
            let resizeTimer;
            window.addEventListener('resize', function() {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(function() {
                    if (window.innerWidth > 768) {
                        sidebar.classList.remove('show');
                        if (sidebarOverlay) sidebarOverlay.classList.remove('show');
                    }
                }, 250);
            });
            
            // Detector de cambios en el sidebar (Livewire puede reemplazar el DOM)
            if (sidebarElement) {
                const observer = new MutationObserver(() => {
                    initializeSidebar();
                });
                observer.observe(sidebarElement, { childList: true, subtree: true });
            }
        }
    </script>

    <!-- Livewire Scripts (incluye Alpine.js automáticamente en v3) -->
    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::scripts(); ?>

</body>
</html>
<?php /**PATH /home/rluis/Escritorio/trabajo_final/rennova/resources/views/layouts/app.blade.php ENDPATH**/ ?>