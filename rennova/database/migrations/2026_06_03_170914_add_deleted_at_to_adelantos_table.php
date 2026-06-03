<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('adelantos', function (Blueprint $table) {
            $table->softDeletes();
        });

        DB::table('adelantos')
            ->where('activo', false)
            ->update(['deleted_at' => now()]);
    }

    public function down(): void
    {
        DB::table('adelantos')
            ->whereNotNull('deleted_at')
            ->update(['activo' => false]);

        Schema::table('adelantos', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
