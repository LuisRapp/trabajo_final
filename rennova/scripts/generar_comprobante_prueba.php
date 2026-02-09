<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$recibo = (object) [
    'id_recibo' => 9999,
    'fecha_emision' => now(),
    'monto_bruto' => 150000.00,
    'descuentos' => 12500.00,
    'monto' => 137500.00,
    'observaciones' => 'Liquidacion de prueba - incluye adelantos',
    'activo' => true,
];

$empleadoNombre = 'Juan Perez';
$empleadoRol = 'Operario de campo';
$empleadoDni = '30123456';
$periodo = '01/02/2026 a 08/02/2026';
$generadoPor = 'Sistema';
$fechaGeneracion = now()->format('d/m/Y H:i');

$options = new Dompdf\Options();
$options->set('defaultFont', 'DejaVu Sans');

$dompdf = new Dompdf\Dompdf($options);
$html = view('recibos.pdf.comprobante', [
    'recibo' => $recibo,
    'empleado_nombre' => $empleadoNombre,
    'empleado_rol' => $empleadoRol,
    'empleado_dni' => $empleadoDni,
    'periodo' => $periodo,
    'generado_por' => $generadoPor,
    'fecha_generacion' => $fechaGeneracion,
])->render();

$dompdf->loadHtml($html, 'UTF-8');
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$outputPath = storage_path('app/comprobante_prueba.pdf');
file_put_contents($outputPath, $dompdf->output());

fwrite(STDOUT, "PDF generado: {$outputPath}\n");
