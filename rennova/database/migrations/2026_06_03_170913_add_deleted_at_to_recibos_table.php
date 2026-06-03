<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recibos', function (Blueprint $table) {
            $table->softDeletes();
        });

        DB::table('recibos')
            ->where('activo', false)
            ->update(['deleted_at' => now()]);
    }

    public function down(): void
    {
        DB::table('recibos')
            ->whereNotNull('deleted_at')
            ->update(['activo' => false]);

        Schema::table('recibos', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
