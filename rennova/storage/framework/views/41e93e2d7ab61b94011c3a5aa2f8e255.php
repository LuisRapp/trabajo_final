<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['colspan' => '1', 'message' => 'No hay registros', 'icon' => 'inbox']));

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

foreach (array_filter((['colspan' => '1', 'message' => 'No hay registros', 'icon' => 'inbox']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
$iconMap = [
    'inbox' => '📭',
    'archive' => '📦',
    'list-ul' => '📋',
    'person' => '👤',
    'truck' => '🚛',
    'exclamation-triangle' => '⚠️',
];
$emoji = $iconMap[$icon] ?? '📭';
?>

<tr>
    <td colspan="<?php echo e($colspan); ?>" class="px-4 py-8 text-center">
        <span class="text-3xl text-slate-300 block mb-2"><?php echo e($emoji); ?></span>
        <p class="text-slate-500 font-medium"><?php echo e($message); ?></p>
    </td>
</tr>
<?php /**PATH /home/rluis/Escritorio/trabajo_final/rennova/resources/views/components/empty-state.blade.php ENDPATH**/ ?>