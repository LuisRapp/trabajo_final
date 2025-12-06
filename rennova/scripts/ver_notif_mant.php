<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
$idMant = 18;
$n = \App\Models\NotificacionSistema::where('mantenimiento_id',$idMant)->first();
if(!$n){echo "No notificación para mantenimiento $idMant\n"; exit;}
echo "Notificación ID: {$n->id}\nUsuario: {$n->user_id}\nTítulo: {$n->titulo}\nLímite: {$n->fecha_limite}\nLeída: ".($n->leida?'Sí':'No')."\nAccionada: ".($n->accionada?'Sí':'No')."\n";