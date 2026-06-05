<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <div class="flex items-center gap-4">
            <label class="text-sm font-semibold text-slate-500 shrink-0">Seleccionar lote:</label>
            <select wire:model.live="loteSeleccionado"
                class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm transition-colors focus:border-brand focus:ring-2 focus:ring-brand/20 max-w-xs">
                <option value="">-- Seleccionar --</option>
                <?php $__currentLoopData = $lotes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $op): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($op['id_lote']); ?>" wire:key="option-<?php echo e($op['id_lote']); ?>">
                        <?php echo e($op['propietario'] ?? ('Lote #' . $op['id_lote'])); ?>

                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
    </div>

    
    <?php if($pronosticoData): ?>
        <div class="mb-8">
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