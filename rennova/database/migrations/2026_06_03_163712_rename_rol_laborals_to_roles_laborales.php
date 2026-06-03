<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('empleados', function (Blueprint $table) {
            $table->dropForeign(['id_rol_laboral']);
        });
        Schema::table('historico_rol_laborals', function (Blueprint $table) {
            $table->dropForeign(['rol_laboral_id']);
        });

        Schema::rename('rol_laborals', 'roles_laborales');

        Schema::table('empleados', function (Blueprint $table) {
            $table->foreign('id_rol_laboral')->references('id_rol_laboral')->on('roles_laborales');
        });
        Schema::table('historico_rol_laborals', function (Blueprint $table) {
            $table->foreign('rol_laboral_id')->references('id_rol_laboral')->on('roles_laborales');
        });
    }

    public function down(): void
    {
        Schema::table('empleados', function (Blueprint $table) {
            $table->dropForeign(['id_rol_laboral']);
        });
        Schema::table('historico_rol_laborals', function (Blueprint $table) {
            $table->dropForeign(['rol_laboral_id']);
        });

        Schema::rename('roles_laborales', 'rol_laborals');

        Schema::table('empleados', function (Blueprint $table) {
            $table->foreign('id_rol_laboral')->references('id_rol_laboral')->on('rol_laborals');
        });
        Schema::table('historico_rol_laborals', function (Blueprint $table) {
            $table->foreign('rol_laboral_id')->references('id_rol_laboral')->on('rol_laborals');
        });
    }
};
