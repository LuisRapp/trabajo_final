<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('empleados', function (Blueprint $table) {
            $table->string('email', 120)->nullable()->after('nombre');
            $table->index('email', 'idx_empleados_email');
        });
    }

    public function down(): void
    {
        Schema::table('empleados', function (Blueprint $table) {
            $table->dropIndex('idx_empleados_email');
            $table->dropColumn('email');
        });
    }
};
