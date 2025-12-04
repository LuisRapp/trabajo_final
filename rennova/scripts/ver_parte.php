<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

use App\Models\ParteDiario;
use App\Models\Carga;
use App\Models\Maquinaria;

$parteId = $argv[1] ?? null;

if ($parteId) {
    $parte = ParteDiario::find($parteId);
} else {
    $parte = ParteDiario::orderBy('id_parte_diario','desc')->first();
}

if (!$parte) { echo "No se encontró ParteDiario.\n"; exit(1);} 

echo "ParteDiario #{$parte->id_parte_diario} fecha {$parte->fecha} lote {$parte->id_lote}\n";

$cargas = Carga::with(['maquinarias'])
    ->where('id_parte_diario',$parte->id_parte_diario)
    ->get();

if ($cargas->isEmpty()) {
    echo "Sin cargas asociadas.\n"; exit(0);
}

foreach ($cargas as $c) {
    echo "- Carga #{$c->id_carga} ticket {$c->ticket} peso_neto {$c->peso_neto} kg fecha {$c->fecha_carga}\n";
    $maqIds = $c->maquinarias->pluck('id_maquinaria')->all();
    echo "  Maquinarias: ".(empty($maqIds)?'Ninguna':implode(',',$maqIds))."\n";
}

// Mostrar odómetros actuales de esas maquinarias
$allMaqIds = $cargas->flatMap(function($c){ return $c->maquinarias->pluck('id_maquinaria'); })->unique()->values();
if ($allMaqIds->isNotEmpty()) {
    echo "\nOdómetros actuales:\n";
    $maqs = Maquinaria::whereIn('id_maquinaria',$allMaqIds)->get();
    foreach ($maqs as $m) {
        echo "  Maq {$m->id_maquinaria} ({$m->modelo}): {$m->toneladas_acumuladas} t (umbral {$m->umbral_toneladas})\n";
    }
}
