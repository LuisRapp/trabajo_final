<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$propId = 20;

echo "=== PROPUESTA #{$propId} ===\n\n";

$prop = DB::table('allocation_proposals')->where('id_allocation_proposal', $propId)->first();
if (!$prop) {
    echo "No se encontró la propuesta #{$propId}\n";
    exit(1);
}

echo "ID Lote: {$prop->id_lote}\n";
echo "Tipo Tarea: {$prop->tipo_tarea}\n";
echo "Team Size Sugerido: {$prop->suggested_team_size}\n";
echo "Machinery Count Sugerido: {$prop->suggested_machinery_count}\n";

echo "\n=== EMPLEADOS PROPUESTOS ===\n";
$emps = DB::table('allocation_proposal_employees')
    ->join('empleados', 'empleados.id_empleado', '=', 'allocation_proposal_employees.id_empleado')
    ->leftJoin('rol_laborals', 'rol_laborals.id_rol_laboral', '=', 'empleados.id_rol_laboral')
    ->where('allocation_proposal_employees.id_allocation_proposal', $propId)
    ->select('allocation_proposal_employees.*', 'empleados.apellido', 'empleados.nombre', 'rol_laborals.nombre as rol_nombre')
    ->get();

echo "Total propuestos: {$emps->count()}\n";
foreach($emps as $e) {
    $selected = $e->selected ? 'SÍ' : 'NO';
    echo "  - {$e->apellido}, {$e->nombre} (Rol: {$e->rol_sugerido} / {$e->rol_nombre}) - Seleccionado: {$selected}\n";
}

echo "\n=== MAQUINARIAS PROPUESTAS ===\n";
$maq = DB::table('allocation_proposal_maquinarias')
    ->join('maquinarias', 'maquinarias.id_maquinaria', '=', 'allocation_proposal_maquinarias.id_maquinaria')
    ->leftJoin('tipo_maquinarias', 'tipo_maquinarias.id_tipo_maquinaria', '=', 'maquinarias.id_tipo_maquinaria')
    ->where('allocation_proposal_maquinarias.id_allocation_proposal', $propId)
    ->select('allocation_proposal_maquinarias.*', 'maquinarias.modelo', 'tipo_maquinarias.nombre as tipo_nombre')
    ->get();

echo "Total propuestas: {$maq->count()}\n";
foreach($maq as $m) {
    $selected = $m->selected ? 'SÍ' : 'NO';
    echo "  - {$m->modelo} (Tipo: {$m->tipo_sugerido} / {$m->tipo_nombre}) - Seleccionada: {$selected}\n";
}

echo "\n";
