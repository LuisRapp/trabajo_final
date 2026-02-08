<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('allocation_proposals', function (Blueprint $table) {
            $table->string('status', 20)->default('draft')->after('meta');
            $table->timestamp('confirmed_at')->nullable()->after('status');
            $table->timestamp('applied_at')->nullable()->after('confirmed_at');

            $table->index(['id_lote', 'status', 'created_at'], 'idx_alloc_prop_lote_status_created');
        });
    }

    public function down(): void
    {
        Schema::table('allocation_proposals', function (Blueprint $table) {
            $table->dropIndex('idx_alloc_prop_lote_status_created');
            $table->dropColumn(['status', 'confirmed_at', 'applied_at']);
        });
    }
};
