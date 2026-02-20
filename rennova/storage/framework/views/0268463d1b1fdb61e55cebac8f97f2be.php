

<?php $__env->startSection('content'); ?>
    <div class="container py-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
            <div>
                <h4 class="mb-0"><i class="bi bi-magic"></i> Recomendaciones automáticas</h4>
                <div class="text-muted small">Lote #<?php echo e($loteId); ?></div>
            </div>
            <div class="d-flex gap-2">
                <a class="btn btn-outline-secondary" href="<?php echo e(route('lotes.index')); ?>">
                    <i class="bi bi-arrow-left"></i> Volver a Lotes
                </a>
                <a class="btn btn-outline-primary" href="<?php echo e(route('lotes.tareas', ['loteId' => $loteId])); ?>">
                    <i class="bi bi-list-check"></i> Planificar tareas
                </a>
            </div>
        </div>
    </div>

    <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('allocation-proposals', ['loteId' => $loteId]);

$__html = app('livewire')->mount($__name, $__params, 'lw-361223257-0', $__slots ?? [], get_defined_vars());

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\trabajo_final\rennova\resources\views\lotes\recomendaciones.blade.php ENDPATH**/ ?>