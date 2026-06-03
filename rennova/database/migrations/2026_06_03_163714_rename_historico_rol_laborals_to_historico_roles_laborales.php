<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('historico_rol_laborals', function (Blueprint $table) {
            $table->dropForeign(['rol_laboral_id']);
        });

        Schema::rename('historico_rol_laborals', 'historico_roles_laborales');

        Schema::table('historico_roles_laborales', function (Blueprint $table) {
            $table->foreign('rol_laboral_id')->references('id_rol_laboral')->on('roles_laborales');
        });
    }

    public function down(): void
    {
        Schema::table('historico_roles_laborales', function (Blueprint $table) {
            $table->dropForeign(['rol_laboral_id']);
        });

        Schema::rename('historico_roles_laborales', 'historico_rol_laborals');

        Schema::table('historico_rol_laborals', function (Blueprint $table) {
            $table->foreign('rol_laboral_id')->references('id_rol_laboral')->on('roles_laborales');
        });
    }
};
