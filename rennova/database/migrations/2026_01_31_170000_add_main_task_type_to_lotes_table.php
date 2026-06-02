<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lotes', function (Blueprint $table) {
            $table->string('main_task_type', 30)->nullable()->after('estado');
            $table->index('main_task_type', 'idx_lotes_main_task_type');
        });
    }

    public function down(): void
    {
        Schema::table('lotes', function (Blueprint $table) {
            $table->dropIndex('idx_lotes_main_task_type');
            $table->dropColumn('main_task_type');
        });
    }
};
