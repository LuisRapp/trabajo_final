<nav class="navbar navbar-expand-lg navbar-dark shadow-sm" style="background-color: #2A6041;">
    <div class="container-fluid">
        <button class="btn text-white me-2" id="sidebarToggle">
            <i class="bi bi-list fs-4" id="toggleIcon"></i>
        </button>
        <a class="navbar-brand fw-bold" href="<?php echo e(route('home')); ?>">Rennova</a>
        <div class="ms-auto d-flex align-items-center gap-3">
            <?php if(auth()->check()): ?>
                <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('notificaciones-campana');

$__html = app('livewire')->mount($__name, $__params, 'lw-2187290138-0', $__slots ?? [], get_defined_vars());

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
            <?php endif; ?>
            <div class="dropdown">
                <a class="text-white text-decoration-none dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                    <i class="bi bi-person-circle me-1"></i> <?php echo e(Auth::user()->name ?? 'Usuario'); ?>

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
            </div>
        </div>
    </div>
</nav>
<?php /**PATH D:\trabajo_final\rennova\resources\views/partials/header.blade.php ENDPATH**/ ?>