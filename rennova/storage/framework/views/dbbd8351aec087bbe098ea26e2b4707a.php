<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'lote' => 'Lote sin especificar',
    'alerta' => 'NORMAL',
    'pronostico' => [],
    'analisisImpacto' => ['diasPerdidos' => 0, 'deficitTn' => 0, 'accionPorcentaje' => 0],
    'recomendacionDetallada' => null,
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
    'lote' => 'Lote sin especificar',
    'alerta' => 'NORMAL',
    'pronostico' => [],
    'analisisImpacto' => ['diasPerdidos' => 0, 'deficitTn' => 0, 'accionPorcentaje' => 0],
    'recomendacionDetallada' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    // Configuración High Density - Colores semánticos al 5%
    $alertConfig = [
        'ACELERAR' => [
            'bg' => 'bg-amber-50/50',
            'text' => 'text-amber-700',
            'dot' => 'bg-amber-500',
            'label' => 'ALERTA',
        ],
        'SUSPENDER' => [
            'bg' => 'bg-red-50/50',
            'text' => 'text-red-700',
            'dot' => 'bg-red-500',
            'label' => 'PARADA',
        ],
        'NORMAL' => [
            'bg' => 'bg-emerald-50/50',
            'text' => 'text-emerald-700',
            'dot' => 'bg-emerald-500',
            'label' => 'OPERATIVO',
        ],
    ];
    
    $currentAlert = $alertConfig[$alerta] ?? $alertConfig['NORMAL'];
    
    $iconMap = [
        'sun' => '☀',
        'storm' => '⛈',
        'fog' => '🌫',
        'cloud' => '☁',
    ];

    // Calcular la ventana de trabajo
    $diasOperativos = 0;
    $diasPerdidos = $analisisImpacto['diasPerdidos'] ?? 0;
    foreach ($pronostico as $dia) {
        if (!($dia['inactivo'] ?? false)) {
            $diasOperativos++;
        }
    }
    $ventana = $diasOperativos . ' dias' ?? '2 dias';

    // Encontrar el día crítico
    $diaCritico = 'Desconocido';
    foreach ($pronostico as $dia) {
        if ($dia['inactivo'] ?? false) {
            $diaCritico = ucfirst($dia['label'] ?? 'proximamente');
            break;
        }
    }
?>

<div class="w-full space-y-3">
    
    <div class="grid grid-cols-1 gap-2 md:grid-cols-4">
        
        <div class="rounded-lg border border-slate-200 <?php echo e($currentAlert['bg']); ?> p-3">
            <div class="flex items-center gap-2">
                <span class="h-2 w-2 rounded-full <?php echo e($currentAlert['dot']); ?>"></span>
                <span class="text-xs font-bold uppercase tracking-wide <?php echo e($currentAlert['text']); ?>"><?php echo e($currentAlert['label']); ?></span>
            </div>
            <div class="mt-2 text-xs text-slate-600">
                <span class="font-medium <?php echo e($currentAlert['text']); ?>"><?php echo e($diaCritico); ?></span> · <?php echo e($ventana); ?>

            </div>
        </div>

        
        <div class="rounded-lg border border-slate-200 bg-white p-3">
            <div class="text-xs font-medium uppercase tracking-wide text-slate-500">DIAS PERDIDOS</div>
            <div class="mt-1 text-2xl font-bold text-slate-900"><?php echo e($analisisImpacto['diasPerdidos'] ?? 0); ?></div>
            <div class="text-xs text-slate-500">Lluvia + barro</div>
        </div>

        
        <div class="rounded-lg border border-slate-200 bg-white p-3">
            <div class="text-xs font-medium uppercase tracking-wide text-slate-500">DEFICIT TN</div>
            <div class="mt-1 text-2xl font-bold text-slate-900"><?php echo e(abs($analisisImpacto['deficitTn'] ?? 0)); ?></div>
            <div class="text-xs text-slate-500">Volumen en riesgo</div>
        </div>

        
        <div class="rounded-lg border border-slate-200 bg-white p-3">
            <?php
                $accionConfig = match($alerta) {
                    'SUSPENDER' => ['text' => 'Suspender', 'color' => 'text-red-700'],
                    'ACELERAR' => ['text' => '+' . ($analisisImpacto['accionPorcentaje'] ?? 0) . '%', 'color' => 'text-amber-700'],
                    default => ['text' => 'Normal', 'color' => 'text-emerald-700']
                };
            ?>
            <div class="text-xs font-medium uppercase tracking-wide text-slate-500">ACCION</div>
            <div class="mt-1 text-2xl font-bold <?php echo e($accionConfig['color']); ?>"><?php echo e($accionConfig['text']); ?></div>
            <div class="text-xs text-slate-500">Sugerida</div>
        </div>
    </div>


    
    <div class="rounded-lg border border-slate-200 bg-white">
        <div class="border-b border-slate-200 px-3 py-2">
            <h3 class="text-xs font-bold uppercase tracking-wide text-slate-700">Pronostico Operativo (7 dias)</h3>
        </div>
        <?php if(count($pronostico) > 0): ?>
            <div class="grid grid-cols-7 divide-x divide-slate-100">
                <?php $__currentLoopData = $pronostico; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dia): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $isInactivo = $dia['inactivo'] ?? false;
                        $bgCell = $isInactivo ? 'bg-red-50/50' : 'bg-white';
                        $textColor = $isInactivo ? 'text-red-700' : 'text-emerald-700';
                        $dotColor = $isInactivo ? 'bg-red-500' : 'bg-emerald-500';
                    ?>
                    <div class="px-2 py-3 <?php echo e($bgCell); ?> text-center">
                        <div class="text-xs font-medium text-slate-500"><?php echo e($dia['label']); ?></div>
                        <div class="my-2 text-lg"><?php echo e($iconMap[$dia['icono']] ?? '☀'); ?></div>
                        <div class="flex items-center justify-center gap-1">
                            <span class="h-1.5 w-1.5 rounded-full <?php echo e($dotColor); ?>"></span>
                            <span class="text-xs font-medium <?php echo e($textColor); ?>"><?php echo e($dia['estado']); ?></span>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php else: ?>
            <div class="px-3 py-4 text-center text-xs text-slate-500">Sin datos</div>
        <?php endif; ?>
    </div>


    
    <?php if($recomendacionDetallada): ?>
    <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2">
        <div class="flex items-start gap-2">
            <svg class="h-4 w-4 flex-shrink-0 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div class="flex-1">
                <div class="text-xs font-medium text-slate-700">Recomendacion del Sistema</div>
                <div class="mt-1 whitespace-pre-line text-xs text-slate-600"><?php echo nl2br(e($recomendacionDetallada)); ?></div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php /**PATH D:\trabajo_final\rennova\resources\views\components\clima\pronostico.blade.php ENDPATH**/ ?>