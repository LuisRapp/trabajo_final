<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * El comportamiento del tipo deja de deducirse del nombre: se guarda
     * explicito para que renombrar una etiqueta no rompa el proceso automatico.
     */
    public function up(): void
    {
        Schema::table('tipo_mantenimientos', function (Blueprint $table) {
            $table->string('comportamiento', 20)->default('otro')->after('nombre');
        });

        DB::table('tipo_mantenimientos')
            ->orderBy('id_tipo_mantenimiento')
            ->get(['id_tipo_mantenimiento', 'nombre'])
            ->each(function ($tipo): void {
                $nombre = mb_strtolower((string) $tipo->nombre);

                $comportamiento = match (true) {
                    str_contains($nombre, 'preventivo') => 'preventivo',
                    str_contains($nombre, 'correctivo') => 'correctivo',
                    default => 'otro',
                };

                DB::table('tipo_mantenimientos')
                    ->where('id_tipo_mantenimiento', $tipo->id_tipo_mantenimiento)
                    ->update(['comportamiento' => $comportamiento]);
            });
    }

    public function down(): void
    {
        Schema::table('tipo_mantenimientos', function (Blueprint $table) {
            $table->dropColumn('comportamiento');
        });
    }
};
