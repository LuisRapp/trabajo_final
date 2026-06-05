<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'editWireClick' => '',
    'deleteWireClick' => '',
    'deleteMessage' => '¿Está seguro?',
    'canEdit' => true,
    'canDelete' => true,
    'editRoute' => null,
]));

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

foreach (array_filter(([
    'editWireClick' => '',
    'deleteWireClick' => '',
    'deleteMessage' => '¿Está seguro?',
    'canEdit' => true,
    'canDelete' => true,
    'editRoute' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div <?php echo e($attributes->merge(['class' => 'flex gap-1 justify-center'])); ?>>
    <?php if($canEdit): ?>
        <?php if($editRoute): ?>
            <a href="<?php echo e($editRoute); ?>" title="Editar" class="inline-flex items-center px-2 py-1 bg-blue-50 text-blue-700 hover:bg-blue-100 rounded transition-colors border border-blue-200">
                ✏️
            </a>
        <?php else: ?>
            <button type="button"
                wire:click="<?php echo e($editWireClick); ?>"
                title="Editar"
                class="inline-flex items-center px-2 py-1 bg-blue-50 text-blue-700 hover:bg-blue-100 rounded transition-colors border border-blue-200">
                ✏️
            </button>
        <?php endif; ?>
    <?php endif; ?>

    <?php if($canDelete): ?>
        <button type="button"
            wire:click="<?php echo e($deleteWireClick); ?>"
            wire:confirm="<?php echo e($deleteMessage); ?>"
            title="Eliminar"
            class="inline-flex items-center px-2 py-1 bg-red-50 text-red-700 hover:bg-red-100 rounded transition-colors border border-red-200">
            🗑️
        </button>
    <?php endif; ?>
</div>
<?php /**PATH /home/rluis/Escritorio/trabajo_final/rennova/resources/views/components/action-buttons.blade.php ENDPATH**/ ?>