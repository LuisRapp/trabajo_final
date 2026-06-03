<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('insumos', function (Blueprint $table) {
            $table->dropForeign(['id_proveedor']);
        });
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropForeign(['id_proveedor']);
        });
        Schema::table('recibos', function (Blueprint $table) {
            $table->dropForeign(['id_proveedor']);
        });
        Schema::table('lotes_inventario', function (Blueprint $table) {
            $table->dropForeign(['id_proveedor']);
        });

        Schema::rename('proveedors', 'proveedores');

        Schema::table('insumos', function (Blueprint $table) {
            $table->foreign('id_proveedor')->references('id_proveedor')->on('proveedores');
        });
        Schema::table('ventas', function (Blueprint $table) {
            $table->foreign('id_proveedor')->references('id_proveedor')->on('proveedores');
        });
        Schema::table('recibos', function (Blueprint $table) {
            $table->foreign('id_proveedor')->references('id_proveedor')->on('proveedores');
        });
        Schema::table('lotes_inventario', function (Blueprint $table) {
            $table->foreign('id_proveedor')->references('id_proveedor')->on('proveedores');
        });
    }

    public function down(): void
    {
        Schema::table('insumos', function (Blueprint $table) {
            $table->dropForeign(['id_proveedor']);
        });
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropForeign(['id_proveedor']);
        });
        Schema::table('recibos', function (Blueprint $table) {
            $table->dropForeign(['id_proveedor']);
        });
        Schema::table('lotes_inventario', function (Blueprint $table) {
            $table->dropForeign(['id_proveedor']);
        });

        Schema::rename('proveedores', 'proveedors');

        Schema::table('insumos', function (Blueprint $table) {
            $table->foreign('id_proveedor')->references('id_proveedor')->on('proveedors');
        });
        Schema::table('ventas', function (Blueprint $table) {
            $table->foreign('id_proveedor')->references('id_proveedor')->on('proveedors');
        });
        Schema::table('recibos', function (Blueprint $table) {
            $table->foreign('id_proveedor')->references('id_proveedor')->on('proveedors');
        });
        Schema::table('lotes_inventario', function (Blueprint $table) {
            $table->foreign('id_proveedor')->references('id_proveedor')->on('proveedors');
        });
    }
};
