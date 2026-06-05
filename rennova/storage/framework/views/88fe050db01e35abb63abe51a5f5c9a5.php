<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['tabs' => [], 'activeTab' => '', 'tabProperty' => 'tab_activo']));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['tabs' => [], 'activeTab' => '', 'tabProperty' => 'tab_activo']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div class="mb-6 flex gap-0">
    <?php $__currentLoopData = $tabs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tab): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php if(($tab['can'] ?? true) === true): ?>
            <?php
                $isActive = $activeTab === $tab['value'];
            ?>
            <button type="button"
                wire:click="$set('<?php echo e($tabProperty); ?>', '<?php echo e($tab['value']); ?>')"
                class="inline-flex items-center gap-2 px-4 py-3 font-semibold text-sm border transition-all <?php echo e($loop->first ? 'rounded-l-lg' : ''); ?> <?php echo e(!$loop->first ? 'border-l-0' : ''); ?> <?php echo e($loop->last ? 'rounded-r-lg' : ''); ?> <?php echo e($isActive ? 'bg-green-800 text-white border-green-800' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'); ?>">
                <?php if(isset($tab['icon'])): ?>
                    <i class="bi bi-<?php echo e($tab['icon']); ?>"></i>
                <?php endif; ?>
                <?php echo e($tab['label']); ?>

            </button>
        <?php endif; ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php /**PATH /home/rluis/Escritorio/trabajo_final/rennova/resources/views/components/tab-nav.blade.php ENDPATH**/ ?>