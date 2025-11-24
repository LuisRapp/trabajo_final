<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Máquinas con odómetros y umbrales ===\n\n";

$maquinarias = \App\Models\Maquinaria::all();

foreach ($maquinarias as $m) {
    echo "ID: {$m->id_maquinaria} - {$m->modelo}\n";
    echo "  Toneladas acumuladas: {$m->toneladas_acumuladas} toneladas\n";
    echo "  Umbral: {$m->umbral_toneladas} toneladas\n";
    
    $diferencia = $m->umbral_toneladas - $m->toneladas_acumuladas;
    
    if ($diferencia <= 0) {
        echo "  ⚠️ ¡UMBRAL ALCANZADO! Se excedió por " . abs($diferencia) . " toneladas\n";
    } else {
        echo "  Faltan: {$diferencia} toneladas para alcanzar umbral\n";
    }
    
    echo "\n";
}
