<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('users') || !Schema::hasTable('usuarios')) {
            return;
        }

        DB::transaction(function () {
            if (Schema::hasTable('notificaciones_sistema')) {
                Schema::table('notificaciones_sistema', function (Blueprint $table) {
                    $table->dropForeign('notificaciones_sistema_user_id_foreign');
                });
            }

            if (Schema::hasTable('configuracion_notificaciones_mantenimiento')) {
                Schema::table('configuracion_notificaciones_mantenimiento', function (Blueprint $table) {
                    $table->dropForeign('configuracion_notificaciones_mantenimiento_user_id_foreign');
                });
            }

            $users = DB::table('users')->get();
            $idMap = [];

            foreach ($users as $user) {
                $existing = DB::table('usuarios')->where('email', $user->email)->first();

                if ($existing) {
                    $idMap[$user->id] = $existing->id;
                    continue;
                }

                $name = trim((string) $user->name);
                $parts = $name === '' ? [] : preg_split('/\s+/', $name);
                $nombre = $parts[0] ?? 'Usuario';
                $apellido = trim(implode(' ', array_slice($parts ?? [], 1)));
                if ($apellido === '') {
                    $apellido = 'Rennova';
                }

                $data = [
                    'nombre' => $nombre,
                    'apellido' => $apellido,
                    'email' => $user->email,
                    'password' => $user->password,
                    'telefono' => null,
                    'activo' => true,
                    'ultimo_acceso' => null,
                    'remember_token' => $user->remember_token ?? null,
                    'created_at' => $user->created_at ?? now(),
                    'updated_at' => $user->updated_at ?? now(),
                ];

                $idExists = DB::table('usuarios')->where('id', $user->id)->exists();
                if (!$idExists) {
                    $data['id'] = $user->id;
                    DB::table('usuarios')->insert($data);
                    $idMap[$user->id] = $user->id;
                } else {
                    $newId = DB::table('usuarios')->insertGetId($data);
                    $idMap[$user->id] = $newId;
                }
            }

            foreach ($idMap as $oldId => $newId) {
                DB::table('model_has_roles')
                    ->where('model_type', 'App\\Models\\User')
                    ->where('model_id', $oldId)
                    ->update([
                        'model_type' => 'App\\Models\\Usuario',
                        'model_id' => $newId,
                    ]);

                DB::table('model_has_permissions')
                    ->where('model_type', 'App\\Models\\User')
                    ->where('model_id', $oldId)
                    ->update([
                        'model_type' => 'App\\Models\\Usuario',
                        'model_id' => $newId,
                    ]);

                if (Schema::hasTable('notificaciones_sistema')) {
                    DB::table('notificaciones_sistema')
                        ->where('user_id', $oldId)
                        ->update(['user_id' => $newId]);
                }

                if (Schema::hasTable('configuracion_notificaciones_mantenimiento')) {
                    DB::table('configuracion_notificaciones_mantenimiento')
                        ->where('user_id', $oldId)
                        ->update(['user_id' => $newId]);
                }

                if (Schema::hasTable('sessions')) {
                    DB::table('sessions')
                        ->where('user_id', $oldId)
                        ->update(['user_id' => $newId]);
                }
            }

            if (Schema::hasTable('notificaciones_sistema')) {
                Schema::table('notificaciones_sistema', function (Blueprint $table) {
                    $table->foreign('user_id')
                        ->references('id')
                        ->on('usuarios')
                        ->onDelete('cascade');
                });
            }

            if (Schema::hasTable('configuracion_notificaciones_mantenimiento')) {
                Schema::table('configuracion_notificaciones_mantenimiento', function (Blueprint $table) {
                    $table->foreign('user_id')
                        ->references('id')
                        ->on('usuarios')
                        ->onDelete('cascade');
                });
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Intentionally left blank to avoid destructive rollbacks of user data.
    }
};
