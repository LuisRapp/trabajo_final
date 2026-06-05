<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Orden de Compra de Mantenimiento</title>
    <style>
        @page {
            margin: 135px 45px 65px 45px;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Helvetica, Arial, sans-serif;
            color: #1f2937;
            font-size: 14px;
            line-height: 1.55;
        }
        header {
            position: fixed;
            top: -105px;
            left: 0;
            right: 0;
            height: 95px;
            border-bottom: 2px solid #1a3d2f;
        }
        footer {
            position: fixed;
            bottom: -35px;
            left: 0;
            right: 0;
            height: 35px;
            border-top: 1px solid #e5ebea;
            color: #6b7280;
            text-align: center;
            font-size: 10px;
            padding-top: 8px;
        }
        .doc-title {
            margin: 0;
            font-size: 20px;
            letter-spacing: .6px;
            color: #1a3d2f;
            font-weight: 700;
            text-transform: uppercase;
        }
        .doc-subtitle {
            margin-top: 4px;
            color: #6b7280;
            font-size: 13px;
        }
        .section-title {
            margin: 18px 0 8px;
            font-size: 16px;
            color: #1a3d2f;
            font-weight: 700;
            text-transform: uppercase;
        }
        .order-summary {
            border: 1px solid #dbe5e1;
            background: #f8faf9;
            padding: 10px 12px;
        }
        .order-title {
            margin: 0;
            font-size: 22px;
            font-weight: 700;
            color: #1a3d2f;
            text-transform: uppercase;
        }
        .order-subtitle {
            margin-top: 6px;
            font-size: 16px;
            color: #374151;
        }
        .order-status {
            margin-top: 4px;
            font-size: 16px;
            color: #1f2937;
            font-weight: 700;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        .table-items td,
        .table-items th {
            border: 1px solid #dbe5e1;
            padding: 10px;
            vertical-align: top;
        }
        .table-items th {
            background: #f3f6f5;
            color: #1a3d2f;
            font-size: 13px;
            text-align: left;
            font-weight: 700;
        }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <?php
        $logoPath = public_path('images/logo_rennova.png');
        $mostrarLogo = is_file($logoPath) && extension_loaded('gd');
        $estadoRaw = strtolower((string) ($proposal->status ?? 'pending'));
        $estado = match ($estadoRaw) {
            'pending' => 'Pendiente',
            'sent' => 'Enviada',
            'approved' => 'Aprobada',
            'rejected' => 'Rechazada',
            default => ucfirst($estadoRaw),
        };
    ?>

    <header>
        <table>
            <tr>
                <td style="width:20%;vertical-align:top;">
                    <?php if($mostrarLogo): ?>
                        <img src="<?php echo e($logoPath); ?>" style="height:58px;" alt="Logo">
                    <?php endif; ?>
                </td>
                <td style="width:80%;text-align:right;vertical-align:top;">
                    <h1 class="doc-title">Orden de Compra</h1>
                    <div class="doc-subtitle">Mantenimiento #<?php echo e($proposal->id_mantenimiento); ?></div>
                    <div class="doc-subtitle">Generado por: Sistema | <?php echo e($generatedAt->format('d/m/Y H:i')); ?></div>
                </td>
            </tr>
        </table>
    </header>

    <footer>
        Documento operativo de compra por faltantes | Rennova
    </footer>

    <main>
        <h2 class="section-title">Datos de la orden</h2>
        <div class="order-summary">
            <p class="order-title">Orden de compra #<?php echo e($proposal->id_mantenimiento_purchase_proposal); ?></p>
            <div class="order-subtitle">Mantenimiento #<?php echo e($proposal->id_mantenimiento); ?></div>
            <div class="order-status">Estado: <?php echo e($estado); ?></div>
            <div class="order-subtitle">Maquinaria: <?php echo e($proposal->maquinaria->modelo ?? 'N/A'); ?></div>
        </div>

        <h2 class="section-title">Detalle de faltantes</h2>
        <table class="table-items">
            <thead>
                <tr>
                    <th style="width:38%;">Insumo</th>
                    <th style="width:20%;" class="text-right">Requerido</th>
                    <th style="width:20%;" class="text-right">Disponible</th>
                    <th style="width:22%;" class="text-right">Faltante</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $proposal->insumos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php $um = $item->insumo->unidadMedida->abreviatura ?? ''; ?>
                    <tr>
                        <td><?php echo e($item->insumo->nombre ?? 'Insumo'); ?></td>
                        <td class="text-right"><?php echo e(number_format((float) $item->cantidad_requerida, 2)); ?> <?php echo e($um); ?></td>
                        <td class="text-right"><?php echo e(number_format((float) $item->stock_disponible, 2)); ?> <?php echo e($um); ?></td>
                        <td class="text-right"><?php echo e(number_format((float) $item->faltante, 2)); ?> <?php echo e($um); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="4" style="text-align:center;color:#6b7280;">No hay faltantes registrados.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </main>
</body>
</html>
<?php /**PATH /home/rluis/Escritorio/trabajo_final/rennova/resources/views/mantenimientos/pdf/orden-compra.blade.php ENDPATH**/ ?>