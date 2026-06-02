<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$emp = App\Models\Empleado::find(2);
if ($emp) {
    echo "Empleado ID 2:\n";
    echo json_encode($emp->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
} else {
    echo "Empleado no encontrado\n";
}
