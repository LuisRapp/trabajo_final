<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Orden de mantenimiento</title>
</head>
<body style="margin:0;padding:0;background:#f5f7f6;font-family:Arial,Helvetica,sans-serif;color:#1f2937;">
    <?php
        $fechaProgramada = $mantenimiento->fecha_programada
            ? \Carbon\Carbon::parse($mantenimiento->fecha_programada)->format('d/m/Y')
            : 'N/A';
        $asignado = $mantenimiento->empleados->first();
        $logoUrl = rtrim((string) config('app.url', ''), '/') . '/images/logo_rennova.png';
        $fechaGeneracion = now()->format('d/m/Y H:i');
    ?>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="padding:18px 10px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:680px;background:#ffffff;border:1px solid #d7e0dc;border-radius:8px;overflow:hidden;">
                    <tr>
                        <td style="background:#1a3d2f;color:#ffffff;padding:14px 16px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="width:70px;vertical-align:middle;">
                                        <img src="<?php echo e($logoUrl); ?>" alt="Rennova" style="max-width:56px;height:auto;display:block;">
                                    </td>
                                    <td style="vertical-align:middle;">
                                        <div style="font-size:18px;font-weight:700;line-height:1.2;">ORDEN DE MANTENIMIENTO</div>
                                        <div style="font-size:12px;opacity:.95;margin-top:2px;">Generado por Sistema | <?php echo e($fechaGeneracion); ?></div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:16px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;font-size:13px;">
                                <tr>
                                    <td style="width:42%;padding:8px;border:1px solid #e5ebea;background:#f8faf9;"><strong>Orden</strong></td>
                                    <td style="padding:8px;border:1px solid #e5ebea;">#<?php echo e($mantenimiento->id_mantenimiento); ?></td>
                                </tr>
                                <tr>
                                    <td style="padding:8px;border:1px solid #e5ebea;background:#f8faf9;"><strong>Maquinaria</strong></td>
                                    <td style="padding:8px;border:1px solid #e5ebea;"><?php echo e($mantenimiento->maquinaria->modelo ?? 'N/A'); ?></td>
                                </tr>
                                <tr>
                                    <td style="padding:8px;border:1px solid #e5ebea;background:#f8faf9;"><strong>Fecha programada</strong></td>
                                    <td style="padding:8px;border:1px solid #e5ebea;"><?php echo e($fechaProgramada); ?></td>
                                </tr>
                                <tr>
                                    <td style="padding:8px;border:1px solid #e5ebea;background:#f8faf9;"><strong>Tipo de mantenimiento</strong></td>
                                    <td style="padding:8px;border:1px solid #e5ebea;"><?php echo e($mantenimiento->tipoMantenimiento->nombre ?? 'Preventivo'); ?></td>
                                </tr>
                                <tr>
                                    <td style="padding:8px;border:1px solid #e5ebea;background:#f8faf9;"><strong>Personal asignado</strong></td>
                                    <td style="padding:8px;border:1px solid #e5ebea;">
                                        <?php if($asignado): ?>
                                            <?php echo e($asignado->apellido); ?>, <?php echo e($asignado->nombre); ?> (<?php echo e($asignado->rolLaboral->nombre ?? 'Sin rol'); ?>)
                                        <?php else: ?>
                                            Sin personal asignado
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            </table>

                            <?php if($purchaseProposal): ?>
                                <div style="margin-top:12px;padding:10px;border:1px solid #f0ddb1;background:#fff7e4;color:#6b4f14;font-size:12px;">
                                    Se genero tambien la orden de compra por faltantes de insumos.
                                </div>
                            <?php endif; ?>

                            <p style="margin:14px 0 0 0;font-size:12px;color:#5f6f79;">
                                Se adjuntan los PDF de respaldo de la orden de mantenimiento y de compra (si aplica).
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
<?php /**PATH /home/rluis/Escritorio/trabajo_final/rennova/resources/views/emails/mantenimiento-orden-generada.blade.php ENDPATH**/ ?>