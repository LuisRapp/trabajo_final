<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('historico_rol_laborals', function (Blueprint $table) {
            if (Schema::hasColumn('historico_rol_laborals', 'costo_diario')) {
                $table->dropColumn('costo_diario');
            }
            $table->decimal('precio_tonelada', 12, 2)->nullable();
            $table->decimal('jornal_diario', 12, 2)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('historico_rol_laborals', function (Blueprint $table) {
            if (Schema::hasColumn('historico_rol_laborals', 'precio_tonelada')) {
                $table->dropColumn('precio_tonelada');
            }
            if (Schema::hasColumn('historico_rol_laborals', 'jornal_diario')) {
                $table->dropColumn('jornal_diario');
            }
            $table->decimal('costo_diario', 12, 2)->nullable();
        });
    }
};
