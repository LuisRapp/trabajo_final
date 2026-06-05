<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['wireClick' => '', 'message' => '¿Está seguro?', 'title' => 'Eliminar']));

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

foreach (array_filter((['wireClick' => '', 'message' => '¿Está seguro?', 'title' => 'Eliminar']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<button type="button"
    wire:click="<?php echo e($wireClick); ?>"
    wire:confirm="<?php echo e($message); ?>"
    title="<?php echo e($title); ?>"
    <?php echo e($attributes->merge(['class' => 'inline-flex items-center px-2 py-1 bg-red-50 text-red-700 hover:bg-red-100 rounded transition-colors border border-red-200'])); ?>>
    🗑️
</button>
<?php /**PATH /home/rluis/Escritorio/trabajo_final/rennova/resources/views/components/confirm-delete.blade.php ENDPATH**/ ?>