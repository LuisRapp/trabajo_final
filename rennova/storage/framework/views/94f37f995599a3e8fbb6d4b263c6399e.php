<div>
    
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body">
            <div class="d-flex align-items-center gap-3">
                <label class="fw-semibold text-muted small mb-0">Seleccionar lote:</label>
                <select wire:model.live="loteSeleccionado" class="form-select form-select-sm" style="max-width: 320px;">
                    <option value="">-- Seleccionar --</option>
                    <?php $__currentLoopData = $lotes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $op): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($op['id_lote']); ?>">
                            <?php echo e($op['propietario'] ?? ('Lote #' . $op['id_lote'])); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
        </div>
    </div>

    
    <?php if($pronosticoData): ?>
        <div class="mb-5">
            <?php if (isset($component)) { $__componentOriginal8e533a9f8b46dcc010c770ef91e454a9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8e533a9f8b46dcc010c770ef91e454a9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.clima.pronostico','data' => ['alerta' => $pronosticoData['alerta'] ?? null,'pronostico' => $pronosticoData['pronostico'] ?? [],'analisisImpacto' => $pronosticoData['analisisImpacto'] ?? [],'lote' => $pronosticoData['loteNombre'] ?? null]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('clima.pronostico'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['alerta' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($pronosticoData['alerta'] ?? null),'pronostico' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($pronosticoData['pronostico'] ?? []),'analisisImpacto' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($pronosticoData['analisisImpacto'] ?? []),'lote' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($pronosticoData['loteNombre'] ?? null)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8e533a9f8b46dcc010c770ef91e454a9)): ?>
<?php $attributes = $__attributesOriginal8e533a9f8b46dcc010c770ef91e454a9; ?>
<?php unset($__attributesOriginal8e533a9f8b46dcc010c770ef91e454a9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8e533a9f8b46dcc010c770ef91e454a9)): ?>
<?php $component = $__componentOriginal8e533a9f8b46dcc010c770ef91e454a9; ?>
<?php unset($__componentOriginal8e533a9f8b46dcc010c770ef91e454a9); ?>
<?php endif; ?>
        </div>
    <?php endif; ?>
</div><?php /**PATH /home/rluis/Escritorio/trabajo_final/rennova/resources/views/livewire/selector-lote.blade.php ENDPATH**/ ?>