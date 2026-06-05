<nav class="navbar navbar-expand-lg navbar-dark shadow-sm" style="background-color: #1f5f3b;">
    <div class="container-fluid">
        <button class="text-white mr-2 p-0 hover:opacity-80" id="sidebarToggle">
            <span id="toggleIcon" class="text-base">☰</span>
        </button>
        <a class="navbar-brand fw-bold text-slate-100 text-sm" href="<?php echo e(route('dashboard')); ?>">Rennova</a>
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
                <a class="text-white no-underline dropdown-toggle text-xs" href="#" role="button" data-bs-toggle="dropdown">
                    👤 <?php echo e($displayName); ?>

                </a>
                <ul class="dropdown-menu dropdown-menu-end text-xs">
                    <li><a class="block px-3 py-1.5 text-sm text-slate-700 hover:bg-slate-100 rounded" href="<?php echo e(route('dashboard')); ?>">⚡ Dashboard</a></li>
                    <li><a class="block px-3 py-1.5 text-sm text-slate-700 hover:bg-slate-100 rounded" href="<?php echo e(route('profile.edit')); ?>">👤 Perfil</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="<?php echo e(route('logout')); ?>">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="block px-3 py-1.5 text-sm text-slate-700 hover:bg-slate-100 rounded">🚪 Cerrar sesión</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>
<?php /**PATH /home/rluis/Escritorio/trabajo_final/rennova/resources/views/partials/header.blade.php ENDPATH**/ ?>