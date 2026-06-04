<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Comprobante de Pago</title>
    <style>
        @page { margin: 40px 50px; }
        * { box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            color: #1f2937;
            font-size: 12px;
            margin: 0;
        }
        header {
            border-bottom: 2px solid #1a3d2f;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .title {
            font-size: 18px;
            font-weight: bold;
            color: #1a3d2f;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .subtitle {
            color: #6b7280;
            margin-top: 4px;
            font-size: 11px;
        }
        .section-title {
            margin: 18px 0 8px;
            font-size: 13px;
            font-weight: bold;
            color: #1a3d2f;
        }
        table { width: 100%; border-collapse: collapse; }
        td, th { padding: 8px; border-bottom: 1px solid #e5e7eb; }
        th { text-align: left; background: #f8f9fa; font-size: 11px; color: #1a3d2f; }
        .kpi { background: #f4f7f6; border-radius: 6px; padding: 10px; text-align: center; }
        .kpi-label { color: #6b7280; font-size: 10px; text-transform: uppercase; }
        .kpi-value { font-size: 14px; font-weight: bold; color: #1a3d2f; }
        .badge {
            display: inline-block;
            padding: 4px 8px;
            font-size: 10px;
            border-radius: 10px;
            background: #1a3d2f;
            color: #fff;
            letter-spacing: 0.3px;
        }
        footer { margin-top: 20px; font-size: 10px; color: #6b7280; text-align: center; }
    </style>
</head>
<body>
    <?php
        $logoPath = public_path('images/logo_rennova.png');
        $mostrarLogo = is_file($logoPath) && extension_loaded('gd');
    ?>

    <header>
        <table>
            <tr>
                <td style="width: 45%;">
                    <?php if($mostrarLogo): ?>
                        <img src="<?php echo e($logoPath); ?>" style="height: 50px;">
                    <?php else: ?>
                        <div class="title">Rennova</div>
                    <?php endif; ?>
                </td>
                <td style="width: 55%; text-align: right;">
                    <div class="title">Comprobante de Pago</div>
                    <div class="subtitle">Recibo #<?php echo e($recibo->id_recibo); ?></div>
                    <div class="subtitle">Fecha emisión: <?php echo e(\Carbon\Carbon::parse($recibo->fecha_emision)->format('d/m/Y H:i')); ?></div>
                    <div class="subtitle">Generado por: <?php echo e($generado_por ?? 'Usuario'); ?></div>
                    <div class="subtitle">Generado el: <?php echo e($fecha_generacion ?? ''); ?></div>
                </td>
            </tr>
        </table>
    </header>

    <div class="section-title">Datos del empleado</div>
    <table>
        <tbody>
            <tr>
                <td><strong>Empleado</strong></td>
                <td><?php echo e($empleado_nombre); ?></td>
                <td><strong>DNI</strong></td>
                <td><?php echo e($empleado_dni ?? 'N/A'); ?></td>
            </tr>
            <tr>
                <td><strong>Tarea</strong></td>
                <td><?php echo e($empleado_rol); ?></td>
                <td><strong>Período</strong></td>
                <td><?php echo e($periodo); ?></td>
            </tr>
        </tbody>
    </table>

    <div class="section-title">Detalle</div>
    <table>
        <tbody>
            <tr>
                <td><?php echo e($recibo->observaciones ?? 'Sin detalle'); ?></td>
            </tr>
        </tbody>
    </table>

    <div class="section-title">Resumen de liquidación</div>
    <table>
        <thead>
            <tr>
                <th>Monto Bruto</th>
                <th>Descuentos</th>
                <th>Monto Neto</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>ARS <?php echo e(number_format($recibo->monto_bruto, 2)); ?></td>
                <td>ARS <?php echo e(number_format($recibo->descuentos ?? 0, 2)); ?></td>
                <td><strong>ARS <?php echo e(number_format($recibo->monto, 2)); ?></strong></td>
            </tr>
        </tbody>
    </table>

    <div class="section-title">Totales</div>
    <table>
        <tbody>
            <tr>
                <td class="kpi" width="33%">
                    <div class="kpi-label">Bruto</div>
                    <div class="kpi-value">ARS <?php echo e(number_format($recibo->monto_bruto, 2)); ?></div>
                </td>
                <td class="kpi" width="33%">
                    <div class="kpi-label">Descuentos</div>
                    <div class="kpi-value">ARS <?php echo e(number_format($recibo->descuentos ?? 0, 2)); ?></div>
                </td>
                <td class="kpi" width="33%">
                    <div class="kpi-label">Neto</div>
                    <div class="kpi-value">ARS <?php echo e(number_format($recibo->monto, 2)); ?></div>
                </td>
            </tr>
        </tbody>
    </table>

    <footer>
        Documento generado automáticamente. Rennova © <?php echo e(date('Y')); ?>

    </footer>
</body>
</html>
<?php /**PATH /home/rluis/Escritorio/trabajo_final/rennova/resources/views/recibos/pdf/comprobante.blade.php ENDPATH**/ ?>