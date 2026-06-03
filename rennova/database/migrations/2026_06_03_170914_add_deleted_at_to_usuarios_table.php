<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->softDeletes();
        });

        DB::table('usuarios')
            ->where('activo', false)
            ->update(['deleted_at' => now()]);
    }

    public function down(): void
    {
        DB::table('usuarios')
            ->whereNotNull('deleted_at')
            ->update(['activo' => false]);

        Schema::table('usuarios', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
