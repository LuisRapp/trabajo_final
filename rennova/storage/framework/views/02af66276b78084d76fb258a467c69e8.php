

<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-0">📊 Estadísticas Forestales</h1>
            <p class="text-muted small mt-1">Análisis de costos, ingresos y rentabilidad por lote</p>
        </div>
    </div>

    <?php if($lotes->isEmpty()): ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i>
            <strong>No hay lotes activos.</strong> Crea un lote primero para ver estadísticas.
        </div>
    <?php else: ?>
        <!-- KPI Cards -->
        <div class="row g-4 mb-4">
            <div class="col-12 col-md-6 col-lg-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <p class="text-muted small mb-1">Precio Promedio Venta</p>
                        <h4 class="mb-0 text-success">$<?php echo e(number_format($estadisticas_globales['precio_promedio'], 2)); ?>/tn</h4>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-lg-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <p class="text-muted small mb-1">Costo Promedio</p>
                        <h4 class="mb-0 text-warning">$<?php echo e(number_format($estadisticas_globales['costo_promedio'], 2)); ?>/tn</h4>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-lg-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <p class="text-muted small mb-1">Punto de Equilibrio</p>
                        <h4 class="mb-0 text-info">$<?php echo e(number_format($estadisticas_globales['punto_equilibrio'], 2)); ?>/tn</h4>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-lg-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <p class="text-muted small mb-1">Rentabilidad Promedio</p>
                        <h4 class="mb-0 <?php echo e($estadisticas_globales['rentabilidad_promedio'] >= 0 ? 'text-success' : 'text-danger'); ?>">
                            $<?php echo e(number_format($estadisticas_globales['rentabilidad_promedio'], 2)); ?>/tn
                        </h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- GRÁFICO 1: Producción vs Punto de Equilibrio (últimos 30 días) -->
        <div class="row g-4 mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-light border-bottom">
                        <h5 class="mb-0">📈 Producción vs Punto de Equilibrio (Últimos 30 días)</h5>
                    </div>
                    <div class="card-body">
                        <div id="chartProduccion"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- GRÁFICOS 2 y 3 en una fila -->
        <div class="row g-4 mb-4">
            <!-- GRÁFICO 2: Distribución de Costos -->
            <div class="col-12 col-lg-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-light border-bottom">
                        <h5 class="mb-0">💰 Distribución de Costos</h5>
                    </div>
                    <div class="card-body">
                        <div id="chartDistribucion"></div>
                    </div>
                </div>
            </div>

            <!-- GRÁFICO 3: Evolución Costo por Tonelada -->
            <div class="col-12 col-lg-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-light border-bottom">
                        <h5 class="mb-0">📉 Evolución Costo por Tonelada (6 meses)</h5>
                    </div>
                    <div class="card-body">
                        <div id="chartEvolucion"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- TABLA: Detalle por Lote -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-light border-bottom">
                <h5 class="mb-0">📋 Detalle por Lote</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" style="table-layout: fixed;">
                        <colgroup>
                            <col style="width: 25%;">
                            <col style="width: 10%;">
                            <col style="width: 12%;">
                            <col style="width: 12%;">
                            <col style="width: 12%;">
                            <col style="width: 12%;">
                            <col style="width: 17%;">
                        </colgroup>
                        <thead class="table-light">
                            <tr>
                                <th>Nombre del Lote</th>
                                <th>Hectáreas</th>
                                <th>Precio Promedio</th>
                                <th>Costo Promedio</th>
                                <th>Punto Equilibrio</th>
                                <th>Rentabilidad</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $lotes_estadisticas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td class="fw-bold text-dark" style="word-break: break-word; padding: 15px;">
                                        <span style="display: block; font-size: 14px;"><?php echo e($stat['nombre'] ?? 'Sin nombre'); ?></span>
                                    </td>
                                    <td style="padding: 15px;"><?php echo e(number_format($stat['hectareas'], 2)); ?> ha</td>
                                    <td style="padding: 15px;">
                                        <span class="badge bg-success-light text-success" style="display: block; word-wrap: break-word;">
                                            $<?php echo e(number_format($stat['precio_promedio'], 2)); ?>/tn
                                        </span>
                                    </td>
                                    <td style="padding: 15px;">
                                        <span class="badge bg-warning-light text-warning" style="display: block; word-wrap: break-word;">
                                            $<?php echo e(number_format($stat['costo_promedio'], 2)); ?>/tn
                                        </span>
                                    </td>
                                    <td style="padding: 15px;">
                                        <span class="badge bg-info-light text-info" style="display: block; word-wrap: break-word;">
                                            $<?php echo e(number_format($stat['punto_equilibrio'], 2)); ?>/tn
                                        </span>
                                    </td>
                                    <td style="padding: 15px;">
                                        <span class="badge <?php echo e($stat['rentabilidad'] >= 0 ? 'bg-success' : 'bg-danger'); ?>-light text-<?php echo e($stat['rentabilidad'] >= 0 ? 'success' : 'danger'); ?>" style="display: block; word-wrap: break-word;">
                                            $<?php echo e(number_format($stat['rentabilidad'], 2)); ?>/tn
                                        </span>
                                    </td>
                                    <td style="padding: 15px;">
                                        <span class="badge <?php echo e($stat['rentabilidad'] >= 0 ? 'bg-success' : 'bg-danger'); ?>" style="display: block; text-align: center;">
                                            <?php echo e($stat['rentabilidad'] >= 0 ? '✓ Rentable' : '✗ No Rentable'); ?>

                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Información y Recomendaciones -->
        <div class="row mt-4 mb-4">
            <div class="col-12 col-lg-6">
                <div class="card border-0 shadow-sm bg-light-info">
                    <div class="card-body">
                        <h6 class="card-title mb-3">
                            <i class="bi bi-info-circle text-info"></i>
                            ¿Cómo interpretar los datos?
                        </h6>
                        <ul class="small mb-0">
                            <li><strong>Precio Promedio:</strong> Precio unitario esperado por tonelada.</li>
                            <li><strong>Costo Promedio:</strong> Costo operacional por tonelada (insumos + maquinaria + mano de obra).</li>
                            <li><strong>Punto de Equilibrio:</strong> Precio mínimo para no perder dinero.</li>
                            <li><strong>Rentabilidad:</strong> Diferencia entre ingreso y costo (Precio - Costo).</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-6">
                <div class="card border-0 shadow-sm bg-light-success">
                    <div class="card-body">
                        <h6 class="card-title mb-3">
                            <i class="bi bi-lightbulb text-success"></i>
                            Recomendaciones
                        </h6>
                        <ul class="small mb-0">
                            <li>✓ Si rentabilidad > 0: El lote es rentable.</li>
                            <li>⚠ Si rentabilidad ≈ 0: Estar atento a variaciones de costo.</li>
                            <li>✗ Si rentabilidad < 0: El lote está perdiendo dinero.</li>
                            <li>📊 Revisa constantemente para optimizar operaciones.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="alert alert-secondary mb-0">
            <i class="bi bi-graph-up"></i>
            <strong>Resumen:</strong> Analizando <?php echo e($estadisticas_globales['total_lotes']); ?> lote(s) activo(s) 
            con rentabilidad promedio de <strong>$<?php echo e(number_format($estadisticas_globales['rentabilidad_promedio'], 2)); ?>/tn</strong>.
        </div>
    <?php endif; ?>
</div>

<!-- ApexCharts CDN -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts@latest/dist/apexcharts.min.js"></script>

<script>
    const puntoEquilibrio = <?php echo e($estadisticas_globales['punto_equilibrio']); ?>;
    const colorRojo = '#EF4444';
    const colorVerde = '#10B981';
    const colorAzul = '#3B82F6';
    const colorAmarillo = '#F59E0B';

    // ========== GRÁFICO 1: Producción vs Punto de Equilibrio ==========
    const optionsProduccion = {
        chart: {
            type: 'bar',
            height: 400,
            toolbar: { show: true, tools: { download: true, selection: true, zoom: true, zoomin: true, zoomout: true, pan: true, reset: true } }
        },
        colors: [colorVerde],
        plotOptions: {
            bar: {
                columnWidth: '70%',
                dataLabels: { position: 'top' }
            }
        },
        dataLabels: {
            enabled: true,
            formatter: (val) => `${val.toFixed(1)}tn`,
            offsetY: -20,
            style: { fontSize: '11px', fontWeight: 600 }
        },
        xaxis: {
            categories: <?php echo json_encode($fechas_30_dias, 15, 512) ?>,
            title: { text: 'Fechas (Últimos 30 días)' }
        },
        yaxis: {
            title: { text: 'Toneladas' },
            min: 0
        },
        stroke: { show: true, width: 2, colors: ['transparent'] },
        fill: { opacity: 0.8 },
        tooltip: {
            y: {
                formatter: (val) => `${val.toFixed(1)} tn`
            }
        },
        annotations: {
            yaxis: [
                {
                    y: puntoEquilibrio,
                    borderColor: colorRojo,
                    label: {
                        borderColor: colorRojo,
                        style: {
                            color: '#fff',
                            background: colorRojo,
                            fontSize: '12px',
                            fontWeight: 600
                        },
                        text: `Punto de Equilibrio: ${puntoEquilibrio.toFixed(2)} tn`,
                        position: 'right'
                    }
                }
            ]
        }
    };

    const seriesProduccion = [
        {
            name: 'Producción (tn)',
            data: <?php echo json_encode($produccion_30_dias, 15, 512) ?>
        }
    ];

    const chartProduccion = new ApexCharts(document.querySelector("#chartProduccion"), 
        { ...optionsProduccion, series: seriesProduccion });
    chartProduccion.render();

    // ========== GRÁFICO 2: Distribución de Costos (Donut) ==========
    const distribucionData = <?php echo json_encode($distribucion_costos, 15, 512) ?>;
    const categoriasCostos = distribucionData.map(d => d.name);
    const valoresCostos = distribucionData.map(d => parseFloat(d.value));

    const optionsDistribucion = {
        chart: {
            type: 'donut',
            height: 350
        },
        colors: ['#F59E0B', '#EF4444', '#3B82F6'],
        labels: categoriasCostos,
        plotOptions: {
            pie: {
                donut: {
                    size: '65%',
                    labels: {
                        show: true,
                        name: {
                            fontSize: '14px',
                            fontWeight: 600
                        },
                        value: {
                            fontSize: '16px',
                            fontWeight: 600,
                            formatter: (val) => `$${parseFloat(val).toFixed(2)}`
                        },
                        total: {
                            show: true,
                            label: 'Costo Total',
                            fontSize: '14px',
                            formatter: function() {
                                return '$' + valoresCostos.reduce((a, b) => a + b, 0).toFixed(2);
                            }
                        }
                    }
                }
            }
        },
        tooltip: {
            y: {
                formatter: (val) => `$${val.toFixed(2)}`
            }
        },
        legend: {
            position: 'bottom',
            fontSize: '13px'
        }
    };

    const chartDistribucion = new ApexCharts(document.querySelector("#chartDistribucion"), 
        { ...optionsDistribucion, series: valoresCostos });
    chartDistribucion.render();

    // ========== GRÁFICO 3: Evolución Costo por Tonelada (Área) ==========
    const optionsEvolucion = {
        chart: {
            type: 'area',
            height: 350,
            toolbar: { show: true },
            zoom: { enabled: true }
        },
        colors: [colorAzul],
        dataLabels: { enabled: false },
        stroke: {
            curve: 'smooth',
            width: 3
        },
        xaxis: {
            categories: <?php echo json_encode($fechas_6_meses, 15, 512) ?>,
            title: { text: 'Período (Últimos 6 meses)' }
        },
        yaxis: {
            title: { text: 'Costo por Tonelada ($/tn)' }
        },
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.7,
                opacityTo: 0.1,
                stops: [0, 100]
            }
        },
        tooltip: {
            y: {
                formatter: (val) => `$${val.toFixed(2)}/tn`
            }
        },
        legend: {
            position: 'top'
        }
    };

    const seriesEvolucion = [
        {
            name: 'Costo Promedio',
            data: <?php echo json_encode($evolucion_6_meses, 15, 512) ?>
        }
    ];

    const chartEvolucion = new ApexCharts(document.querySelector("#chartEvolucion"), 
        { ...optionsEvolucion, series: seriesEvolucion });
    chartEvolucion.render();
</script>

<style>
    .bg-success-light { background-color: #d4edda; }
    .bg-warning-light { background-color: #fff3cd; }
    .bg-info-light { background-color: #d1ecf1; }
    .bg-danger-light { background-color: #f8d7da; }
    .bg-light-info { background-color: #e7f3ff; }
    .bg-light-success { background-color: #e7f5e7; }
    
    .apexcharts-legend {
        padding: 15px 0 !important;
    }
    
    table tbody td {
        vertical-align: middle;
        padding: 12px 15px;
    }
    
    table thead th {
        font-weight: 600;
        text-transform: uppercase;
        font-size: 12px;
        letter-spacing: 0.5px;
    }
</style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/reportes/estadisticas-forestales.blade.php ENDPATH**/ ?>