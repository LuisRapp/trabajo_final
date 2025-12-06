<?php
    $lotes = $lotes ?? \App\Models\Lote::where('estado', 'activo')->get();
    $loteSeleccionado = $loteSeleccionado ?? ($lotes->first() ?? null);
?>

<?php if($lotes && $lotes->count() > 0): ?>
<div class="card mb-4 border-0 shadow-sm" style="background-color: #f8f9fa; border: 2px solid var(--primary-color);">
    <div class="card-body">
        <form method="GET" action="<?php echo e(route('home')); ?>" class="d-flex align-items-center gap-3">
            <label class="fw-semibold text-dark small mb-0" style="min-width: fit-content;">📍 Seleccionar lote:</label>
            <select name="lote" class="form-select form-select-sm" style="max-width: 350px; border: 2px solid var(--primary-color);">
                <?php $__currentLoopData = $lotes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $op): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($op->id_lote); ?>" <?php if(optional($loteSeleccionado)->id_lote === $op->id_lote): echo 'selected'; endif; ?>>
                        <?php echo e($op->propietario ?? ('Lote #' . $op->id_lote)); ?>

                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <button type="submit" class="btn btn-sm btn-primary" style="background-color: var(--primary-color); border: none;">Actualizar</button>
        </form>
    </div>
</div>
<?php else: ?>
<div class="alert alert-warning mb-4">
    <i class="bi bi-exclamation-triangle"></i> No hay lotes activos disponibles
</div>
<?php endif; ?>
<?php /**PATH D:\trabajo_final\rennova\resources\views/partials/selector-lote.blade.php ENDPATH**/ ?>