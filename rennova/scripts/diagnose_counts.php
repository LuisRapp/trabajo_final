<?php
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$counts = [
    'lotes' => App\Models\Lote::count(),
    'partes' => App\Models\ParteDiario::count(),
    'cargas' => App\Models\Carga::count(),
    'ventas' => App\Models\Venta::count(),
];

echo json_encode($counts, JSON_PRETTY_PRINT) . PHP_EOL;
