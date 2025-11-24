<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$user2 = \App\Models\User::find(2);
if(!$user2){ echo "❌ No existe usuario 2.\n"; exit(1);} else { echo "Usuario 2: {$user2->name}\n"; }

// Configuración de notificación de umbral para user 2
$cfgExists = \Illuminate\Support\Facades\DB::table('configuracion_notificaciones_mantenimiento')
  ->where('user_id',2)->where('tipo_notificacion','umbral')->exists();
if(!$cfgExists){
  \Illuminate\Support\Facades\DB::table('configuracion_notificaciones_mantenimiento')->insert([
    'user_id'=>2,
    'tipo_notificacion'=>'umbral',
    'created_at'=>now(),
    'updated_at'=>now()
  ]);
  echo "✅ Configuración de umbral creada para usuario 2.\n";
} else {
  echo "ℹ️ Configuración de umbral ya existía para usuario 2.\n";
}

$mantenimientoId = 18; // Orden existente
$notifOriginal = \App\Models\NotificacionSistema::where('mantenimiento_id',$mantenimientoId)->first();
if(!$notifOriginal){ echo "⚠️ No se encontró notificación original del mantenimiento $mantenimientoId.\n"; exit; }

// Verificar si ya hay notificación para user 2 y este mantenimiento
$ya = \App\Models\NotificacionSistema::where('mantenimiento_id',$mantenimientoId)->where('user_id',2)->first();
if($ya){
  echo "ℹ️ Ya existe notificación para usuario 2 y mantenimiento $mantenimientoId (ID {$ya->id}).\n";
} else {
  $nueva = \App\Models\NotificacionSistema::create([
    'user_id'=>2,
    'mantenimiento_id'=>$mantenimientoId,
    'tipo'=>$notifOriginal->tipo,
    'titulo'=>$notifOriginal->titulo,
    'mensaje'=>$notifOriginal->mensaje,
    'fecha_limite'=>$notifOriginal->fecha_limite,
  ]);
  echo "✅ Notificación duplicada ID {$nueva->id} para usuario 2.\n";
}

// Listar últimas notificaciones de usuario 2
$lista = \App\Models\NotificacionSistema::where('user_id',2)->latest()->take(5)->get();
echo "\n=== Últimas notificaciones usuario 2 ===\n";
foreach($lista as $n){
  echo "- ID {$n->id} | mant {$n->mantenimiento_id} | {$n->titulo} | venc: ".$n->fecha_limite." | leída: ".($n->leida?'Sí':'No')."\n";
}
