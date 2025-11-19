<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('choferes', 'id_cliente')) {
            Schema::table('choferes', function (Blueprint $table) {
                $table->unsignedBigInteger('id_cliente')->nullable();
                $table->foreign('id_cliente')->references('id_cliente')->on('clientes');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('choferes', 'id_cliente')) {
            Schema::table('choferes', function (Blueprint $table) {
                $table->dropForeign(['id_cliente']);
                $table->dropColumn('id_cliente');
            });
        }
    }
};
