<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title><?php echo e($reporte_nombre ?? 'Reporte Forestal'); ?></title>
    <style>
        @page {
            margin: 140px 50px 70px 50px;
            counter-reset: page 1;
        }
        * { box-sizing: border-box; }
        body {
            font-family: 'Helvetica', sans-serif;
            color: #2c3e50;
            line-height: 1.5;
            margin: 0;
        }
        header {
            position: fixed;
            top: -110px;
            left: 0px;
            right: 0px;
            height: 100px;
            border-bottom: 3px solid #1a3d2f;
        }
        footer {
            position: fixed;
            bottom: -40px;
            left: 0px;
            right: 0px;
            height: 50px;
            font-size: 10px;
            color: #7f8c8d;
            text-align: center;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }
        .pagenum:before { content: ""; }
        .title-report {
            font-size: 22px;
            font-weight: bold;
            color: #1a3d2f;
            text-transform: uppercase;
            margin: 0;
            letter-spacing: 1px;
        }
        .subtitle-report {
            font-size: 12px;
            color: #7f8c8d;
            margin-top: 5px;
        }
        h3 {
            font-size: 16px;
            color: #1a3d2f;
            border-left: 5px solid #1a3d2f;
            padding-left: 10px;
            margin-top: 30px;
            text-transform: uppercase;
        }
        .table-data {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .table-data th {
            background-color: #f8f9fa;
            color: #1a3d2f;
            font-weight: bold;
            text-align: left;
            padding: 10px;
            border-bottom: 2px solid #1a3d2f;
            font-size: 11px;
        }
        .table-data td {
            padding: 10px;
            border-bottom: 1px solid #eee;
            font-size: 12px;
        }
        .kpi-container {
            margin-top: 20px;
            width: 100%;
        }
        .kpi-card {
            background: #f4f7f6;
            padding: 15px;
            border-radius: 5px;
            text-align: center;
        }
        .kpi-value {
            font-size: 18px;
            font-weight: bold;
            color: #1a3d2f;
            display: block;
        }
        .kpi-label {
            font-size: 10px;
            color: #7f8c8d;
            text-transform: uppercase;
        }
    </style>
</head>
<body>
    <?php
        $kpiProduccion = $tipo === 'lote' ? ($produccion_toneladas ?? 0) : ($produccion_total ?? 0);
        $kpiRendimiento = $tipo === 'lote' ? ($rendimiento ?? 0) : ($rendimiento_promedio ?? 0);
        $costoPromedio = $tipo === 'lote'
            ? ($indicadores['costo_promedio'] ?? 0)
            : ($estadisticas_globales['costo_promedio'] ?? 0);

        $logoPath = public_path('images/logo_rennova.png');
        $mostrarLogo = is_file($logoPath) && extension_loaded('gd');

        $tablaLotes = $lotes_estadisticas ?? [];
        if ($tipo === 'lote' && isset($lote)) {
            $tablaLotes = [[
                'nombre' => $lote->ubicacion ?? $lote->propietario ?? ('Lote ' . $lote->id_lote),
                'hectareas' => $lote->superficie ?? 0,
                'produccion' => $produccion_toneladas ?? 0,
                'costo_promedio' => $indicadores['costo_promedio'] ?? 0,
                'rentabilidad' => $indicadores['rentabilidad'] ?? 0,
            ]];
        }
    ?>

    <header>
        <table width="100%">
            <tr>
                <td width="50%">
                    <?php if($mostrarLogo): ?>
                        <img src="<?php echo e($logoPath); ?>" style="height: 55px;">
                    <?php else: ?>
                        <div class="title-report">Rennova</div>
                    <?php endif; ?>
                </td>
                <td width="50%" style="text-align: right; vertical-align: middle;">
                    <div class="title-report">Reporte Forestal</div>
                    <div class="subtitle-report">Generado el: <?php echo e($fecha_hora); ?></div>
                    <div class="subtitle-report">Generado por: <?php echo e($generado_por ?? 'Usuario'); ?></div>
                </td>
            </tr>
        </table>
    </header>

    <footer>
        <table width="100%">
            <tr>
                <td width="40%" style="text-align: left;">Rennova © <?php echo e(date('Y')); ?></td>
                <td width="20%" style="text-align: center;" class="pagenum"></td>
                <td width="40%" style="text-align: right;">Confidencial</td>
            </tr>
        </table>
    </footer>

    <main>
        <?php if($tipo === 'lote' && isset($lote)): ?>
            <h3>Información del Lote</h3>
            <table class="table-data">
                <tbody>
                    <tr>
                        <td><strong>Identificación</strong></td>
                        <td><?php echo e($lote->ubicacion ?? $lote->propietario ?? ('Lote ' . $lote->id_lote)); ?></td>
                        <td><strong>Propietario</strong></td>
                        <td><?php echo e($lote->propietario ?? 'Sin datos'); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Condición de compra</strong></td>
                        <td><?php echo e($lote->condicion_compra ?? 'Sin datos'); ?></td>
                        <td><strong>Especie</strong></td>
                        <td><?php echo e($lote->especie ?? 'Sin datos'); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Superficie</strong></td>
                        <td><?php echo e(number_format($lote->superficie ?? 0, 2)); ?> Ha</td>
                        <td><strong>Estado</strong></td>
                        <td><?php echo e($lote->estado ?? 'Sin datos'); ?></td>
                    </tr>
                </tbody>
            </table>

            <h3>Empleados Asignados</h3>
            <table class="table-data">
                <thead>
                    <tr>
                        <th>Empleado</th>
                        <th>Rol</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = ($lote->empleados ?? collect()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $empleado): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e(($empleado->apellido ?? '') . ' ' . ($empleado->nombre ?? '')); ?></td>
                            <td><?php echo e($empleado->rolLaboral->nombre ?? $empleado->rolLaboral->descripcion ?? 'Sin rol'); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="2">Sin empleados asignados</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <h3>Maquinarias Asignadas</h3>
            <table class="table-data">
                <thead>
                    <tr>
                        <th>Tipo</th>
                        <th>Modelo</th>
                        <th>Estado</th>
                        <th>Alquilada</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = ($lote->maquinarias ?? collect()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $maquinaria): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($maquinaria->tipoMaquinaria->nombre ?? $maquinaria->tipoMaquinaria->descripcion ?? 'Sin tipo'); ?></td>
                            <td><?php echo e($maquinaria->modelo ?? 'Sin modelo'); ?></td>
                            <td><?php echo e($maquinaria->estado ?? 'Sin estado'); ?></td>
                            <td><?php echo e(($maquinaria->es_alquilada ?? false) ? 'Sí' : 'No'); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="4">Sin maquinarias asignadas</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <table class="kpi-container" width="100%" cellspacing="10">
            <tr>
                <td class="kpi-card" width="33%">
                    <span class="kpi-label">Producción Total</span>
                    <span class="kpi-value"><?php echo e(number_format($kpiProduccion, 2)); ?> Tn</span>
                </td>
                <td class="kpi-card" width="33%">
                    <span class="kpi-label">Rendimiento Promedio</span>
                    <span class="kpi-value"><?php echo e(number_format($kpiRendimiento, 2)); ?> Tn/Ha</span>
                </td>
                <td class="kpi-card" width="33%">
                    <span class="kpi-label">Costo Promedio</span>
                    <span class="kpi-value">ARS <?php echo e(number_format($costoPromedio, 2)); ?></span>
                </td>
            </tr>
        </table>

        <h3>Resumen Financiero del Periodo</h3>
        <table class="table-data">
            <tbody>
                <tr>
                    <td><strong>Ingresos por ventas</strong></td>
                    <td>ARS <?php echo e(number_format($ingresos_periodo ?? 0, 2)); ?></td>
                    <td><strong>Gastos del periodo</strong></td>
                    <td>ARS <?php echo e(number_format($gastos_periodo ?? 0, 2)); ?></td>
                </tr>
            </tbody>
        </table>

        <h3>Desglose Detallado por Lote</h3>
        <table class="table-data">
            <thead>
                <tr>
                    <th>Identificación del Lote</th>
                    <th>Superficie</th>
                    <th>Producción</th>
                    <th>Costo/Tn</th>
                    <th>Rentabilidad</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $tablaLotes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><strong><?php echo e($item['nombre'] ?? 'Sin nombre'); ?></strong></td>
                    <td><?php echo e($item['hectareas'] ?? 0); ?> Ha</td>
                    <td><?php echo e(number_format($item['produccion'] ?? 0, 2)); ?> Tn</td>
                    <td>ARS <?php echo e(number_format($item['costo_promedio'] ?? 0, 2)); ?></td>
                    <td style="color: <?php echo e(($item['rentabilidad'] ?? 0) >= 0 ? '#27ae60' : '#e74c3c'); ?>">
                        <strong>ARS <?php echo e(number_format($item['rentabilidad'] ?? 0, 2)); ?></strong>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </main>
</body>
</html>
<?php /**PATH D:\trabajo_final\rennova\resources\views\reportes\pdf\estadisticas-forestales.blade.php ENDPATH**/ ?>