<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$maquinariaId = 2;
$toneladas = 12; // supera umbral 10

$lote = \App\Models\Lote::first();
if(!$lote){
    echo "No hay lote disponible\n"; exit(1);
}

$carga = \App\Models\Carga::create([
    'id_lote'=>$lote->id_lote,
    'id_maquinaria'=>$maquinariaId,
    'id_categoria_madera'=>1,
    'toneladas'=>$toneladas,
    'fecha_carga'=>now(),
    'numero_remito'=>'TEST-AUTO-'.now()->format('YmdHis'),
    'observaciones'=>'Prueba disparo umbral',
    'estado'=>'pendiente'
]);

echo "Carga creada ID {$carga->id_carga} toneladas {$carga->toneladas}\n";
