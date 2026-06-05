<nav class="navbar navbar-expand-lg navbar-dark shadow-sm" style="background-color: #1f5f3b;">
    <div class="container-fluid">
        <button class="btn text-white me-2 p-0" id="sidebarToggle">
            <i class="bi bi-list" id="toggleIcon" style="font-size: 1.1rem;"></i>
        </button>
        <a class="navbar-brand fw-bold text-slate-100" href="<?php echo e(route('dashboard')); ?>" style="font-size: 0.9rem;">Rennova</a>
        <div class="ms-auto d-flex align-items-center gap-3">
            <?php if(auth()->check()): ?>
                <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('notificaciones-campana');

$__html = app('livewire')->mount($__name, $__params, 'lw-3134625742-0', $__slots ?? [], get_defined_vars());

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
            <?php endif; ?>
            <div class="dropdown">
                <?php
                    $user = auth()->user();
                    $displayName = $user?->name
                        ?? trim(($user?->nombre ?? '') . ' ' . ($user?->apellido ?? ''))
                        ?: 'Usuario';
                ?>
                <a class="text-white text-decoration-none dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" style="font-size: 0.8rem;">
                    <i class="bi bi-person-circle me-1" style="font-size: 0.9rem;"></i> <?php echo e($displayName); ?>

                </a>
                <ul class="dropdown-menu dropdown-menu-end" style="font-size: 0.85rem;">
                    <li><a class="dropdown-item" href="<?php echo e(route('dashboard')); ?>" style="padding: 0.4rem 1rem;"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                    <li><a class="dropdown-item" href="<?php echo e(route('profile.edit')); ?>" style="padding: 0.4rem 1rem;"><i class="bi bi-person"></i> Perfil</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="<?php echo e(route('logout')); ?>">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="dropdown-item" style="padding: 0.4rem 1rem;"><i class="bi bi-box-arrow-right"></i> Cerrar sesión</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>
<?php /**PATH /home/rluis/Escritorio/trabajo_final/rennova/resources/views/partials/header.blade.php ENDPATH**/ ?>