<div class="p-6 space-y-6">
    <div class="bg-white shadow rounded p-4 flex flex-col gap-3">
        <div class="flex items-center justify-between gap-3">
            <h2 class="text-lg font-semibold text-gray-800">Seleccionar lote</h2>
            <button
                type="button"
                wire:click="refreshStats"
                wire:loading.attr="disabled"
                class="inline-flex items-center gap-2 rounded border border-indigo-200 bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700 hover:bg-indigo-100 focus:outline-none focus:ring-2 focus:ring-indigo-300"
            >
                <span wire:loading.remove wire:target="refreshStats">&#8635; Actualizar</span>
                <span wire:loading wire:target="refreshStats" class="flex items-center gap-1">
                    <svg class="animate-spin h-4 w-4 text-indigo-700" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>
                    Actualizando...
                </span>
            </button>
        </div>
        <select wire:model.live="loteId" class="border rounded px-3 py-2 text-sm focus:outline-none focus:ring focus:border-indigo-300">
            <option value="">-- Elegí un lote --</option>
            <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $lotes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lote): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($lote->id_lote); ?>">#<?php echo e($lote->id_lote); ?> - <?php echo e($lote->propietario); ?> (<?php echo e($lote->ubicacion); ?>)</option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
        </select>
        <!--[if BLOCK]><![endif]--><?php if(!$lotes->count()): ?>
            <p class="text-sm text-gray-500">No hay lotes cargados aún.</p>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white shadow rounded p-4">
            <p class="text-sm text-gray-500">Precio Promedio Venta</p>
            <p class="text-2xl font-semibold text-gray-900">$ <?php echo e(number_format($precioPromedio ?? 0, 2)); ?></p>
        </div>
        <div class="bg-white shadow rounded p-4">
            <p class="text-sm text-gray-500">Costo Promedio / Tn</p>
            <p class="text-2xl font-semibold text-gray-900">$ <?php echo e(number_format($costoPromTn ?? 0, 2)); ?></p>
        </div>
        <div class="bg-white shadow rounded p-4">
            <p class="text-sm text-gray-500">Punto de Equilibrio Diario</p>
            <p class="text-2xl font-semibold text-gray-900"><?php echo e(number_format($puntoEquilibrio ?? 0, 2)); ?> tn</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="bg-white shadow rounded p-4 lg:col-span-2">
            <h3 class="text-lg font-semibold text-gray-800 mb-2">Rendimiento vs Equilibrio (Últimos 30 días)</h3>
            <div id="chart-produccion" class="w-full h-80" wire:ignore></div>
            <!--[if BLOCK]><![endif]--><?php if(empty($produccion['data']) || count($produccion['data']) === 0): ?>
                <p class="text-sm text-gray-500 mt-2">Sin datos de producción para este lote.</p>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
        </div>
        <div class="bg-white shadow rounded p-4">
            <h3 class="text-lg font-semibold text-gray-800 mb-2">Composición de Costos</h3>
            <div id="chart-composicion" class="w-full h-80" wire:ignore></div>
            <!--[if BLOCK]><![endif]--><?php if(empty($composicion['data']) || count($composicion['data']) === 0): ?>
                <p class="text-sm text-gray-500 mt-2">Sin datos de costos para este lote.</p>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
        </div>
    </div>

    <div class="bg-white shadow rounded p-4">
        <h3 class="text-lg font-semibold text-gray-800 mb-2">Evolución de Costo Unitario (Últimos 6 meses)</h3>
        <div id="chart-costo" class="w-full h-72" wire:ignore></div>
        <!--[if BLOCK]><![endif]--><?php if(empty($costoSerie['data']) || count($costoSerie['data']) === 0): ?>
            <p class="text-sm text-gray-500 mt-2">Sin datos de evolución de costos para este lote.</p>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
    </div>
</div>

<script id="stats-payload" type="application/json">
<?php echo json_encode([
    'produccion' => [
        'labels' => $produccion['fechas'] ?? [],
        'data' => $produccion['data'] ?? [],
    ],
    'composicion' => [
        'labels' => $composicion['labels'] ?? [],
        'data' => $composicion['data'] ?? [],
    ],
    'costoSerie' => [
        'labels' => $costoSerie['labels'] ?? [],
        'data' => $costoSerie['data'] ?? [],
    ],
    'puntoEquilibrio' => $puntoEquilibrio ?? 0,
]); ?>

</script>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    window.statsCharts = window.statsCharts || {};

    function renderStatsCharts() {
        const payloadEl = document.getElementById('stats-payload');
        if (!payloadEl) return;
        let payload = {};
        try { payload = JSON.parse(payloadEl.textContent || '{}'); } catch (e) { return; }

        const produccionLabels = payload.produccion?.labels || [];
        const produccionData = payload.produccion?.data || [];
        const puntoEquilibrio = Number(payload.puntoEquilibrio || 0);
        const composicionLabels = payload.composicion?.labels || [];
        const composicionData = payload.composicion?.data || [];
        const costoLabels = payload.costoSerie?.labels || [];
        const costoData = payload.costoSerie?.data || [];

        const chartProdEl = document.querySelector('#chart-produccion');
        if (chartProdEl) {
            if (window.statsCharts.prod) window.statsCharts.prod.destroy();
            window.statsCharts.prod = new ApexCharts(chartProdEl, {
                chart: { type: 'bar', height: 320, toolbar: { show: false } },
                series: [{ name: 'Producción (tn)', data: produccionData }],
                xaxis: { categories: produccionLabels },
                yaxis: { title: { text: 'Toneladas' } },
                annotations: {
                    yaxis: [{
                        y: puntoEquilibrio,
                        borderColor: '#ef4444',
                        label: {
                            borderColor: '#ef4444',
                            style: { color: '#fff', background: '#ef4444' },
                            text: `Meta de Equilibrio (${puntoEquilibrio} Tn)`
                        }
                    }]
                },
                dataLabels: { enabled: false },
                colors: ['#2563eb']
            });
            window.statsCharts.prod.render();
        }

        const chartCompEl = document.querySelector('#chart-composicion');
        if (chartCompEl) {
            if (window.statsCharts.comp) window.statsCharts.comp.destroy();
            window.statsCharts.comp = new ApexCharts(chartCompEl, {
                chart: { type: 'donut', height: 320 },
                series: composicionData,
                labels: composicionLabels,
                legend: { position: 'bottom' },
                colors: ['#10b981', '#6366f1', '#f59e0b', '#ef4444', '#3b82f6', '#8b5cf6']
            });
            window.statsCharts.comp.render();
        }

        const chartCostoEl = document.querySelector('#chart-costo');
        if (chartCostoEl) {
            if (window.statsCharts.costo) window.statsCharts.costo.destroy();
            window.statsCharts.costo = new ApexCharts(chartCostoEl, {
                chart: { type: 'line', height: 300, toolbar: { show: false } },
                series: [{ name: 'Costo / Tn', data: costoData }],
                xaxis: { categories: costoLabels },
                stroke: { width: 3, curve: 'smooth' },
                markers: { size: 4 },
                colors: ['#0ea5e9'],
                dataLabels: { enabled: false },
                yaxis: { labels: { formatter: (val) => `$ ${val}` } }
            });
            window.statsCharts.costo.render();
        }
    }

    document.addEventListener('livewire:init', () => {
        renderStatsCharts();
        Livewire.hook('message.processed', () => {
            renderStatsCharts();
        });
    });
</script><?php /**PATH /var/www/html/resources/views/livewire/stats-dashboard.blade.php ENDPATH**/ ?>