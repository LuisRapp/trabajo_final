<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
$m=\App\Models\Maquinaria::find(2); if(!$m){echo "No existe maquinaria 2\n"; exit;} echo "Maquinaria 2 toneladas_acumuladas: {$m->toneladas_acumuladas} umbral: {$m->umbral_toneladas}\n";