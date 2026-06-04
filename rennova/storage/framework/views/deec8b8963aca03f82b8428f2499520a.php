<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-3 fw-bold" style="color: #2A6041;">
                <i class="bi bi-bell-fill"></i> Mis Notificaciones
            </h1>
        </div>
    </div>

    <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('notificaciones-sistema');

$__html = app('livewire')->mount($__name, $__params, 'lw-2645167375-0', $__slots ?? [], get_defined_vars());

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/rluis/Escritorio/trabajo_final/rennova/resources/views/notificaciones/index.blade.php ENDPATH**/ ?>