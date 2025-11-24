<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "== Configuración notificaciones umbral ==\n";
$user = \App\Models\User::first();
if(!$user){ echo "No hay usuarios.\n"; exit(1);} 
$exists = \Illuminate\Support\Facades\DB::table('configuracion_notificaciones_mantenimiento')
    ->where('user_id',$user->id)
    ->where('tipo_notificacion','umbral')
    ->exists();
if($exists){
    echo "Ya existía configuración para usuario {$user->id}.\n";
}else{
    \Illuminate\Support\Facades\DB::table('configuracion_notificaciones_mantenimiento')->insert([
        'user_id'=>$user->id,
        'tipo_notificacion'=>'umbral',
        'created_at'=>now(),
        'updated_at'=>now()
    ]);
    echo "Configuración creada para usuario {$user->id}.\n";
}
